<?php

namespace App\Commands;

use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use function Termwind\{render};
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle as Adapter;
use Cloudflare\API\Endpoints\{DNS,User,Zones};

class CloudflareCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = "update {dns_record_name? : Cloudflare API Token} {target_ip? : DNS Record Target IP} {zone_name? : DNS Zone Name}";

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the default Cloudflare record IP to current Public IP';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->comment('running update', 'v');

        $target_ip = $this->argument('target_ip');
        if(empty($target_ip)){
            $target_ip = Http::get(env('PUBLIC_IP_CHECK_API', 'https://ip.andp97.dev/api/v2'))['data']['ip'] ?? null;
        }

        if(filter_var($target_ip, FILTER_VALIDATE_IP) === false){
            $this->error('Invalid param [target_ip]: '. strtolower(config('app.name')) .' <dns_record_name> <target_ip> <zone_name>');
            exit(255);
        }

        $this->comment(sprintf('target_ip: %s', $target_ip), 'v');

        $dns_record_name = $this->argument('dns_record_name');

        if(empty($dns_record_name)){
            $dns_record_name = env('DNS_RECORD_NAME');
        }

        $this->comment(sprintf('dns_record_name: %s', $dns_record_name),'v');

        if(empty($dns_record_name)){
            $this->error('Missing Required param [dns_record_name]: '. strtolower(config('app.name')) .' <dns_record_name> <target_ip> <zone_name>');
            exit(255);
        }

        if(filter_var($dns_record_name, FILTER_VALIDATE_DOMAIN) === false){
            $this->error('Invalid param [dns_record_name]: '. strtolower(config('app.name')) .' <dns_record_name> <target_ip> <zone_name>');
            exit(255);
        }

        $zone_name = $this->argument('zone_name');
        if(empty($zone_name)){
            $zone_name = env('CLOUDFLARE_ZONE_NAME');
        }
        $this->comment(sprintf('zone_name: %s', $zone_name),'v');
        if(empty($zone_name)){
            $this->error('Missing Required param [zone_name]: '. strtolower(config('app.name')) .' <dns_record_name> <target_ip> <zone_name>');
            exit(255);
        }

        if(filter_var($zone_name, FILTER_VALIDATE_DOMAIN) === false){
            $this->error('Invalid param [zone_name]: '. strtolower(config('app.name')) .' <dns_record_name> <target_ip> <zone_name>');
            exit(255);
        }

        $api_token = env('CLOUDFLARE_TOKEN');
        if(empty($api_token)){
            $this->error('Missing [CLOUDFLARE_TOKEN] from env');
            exit(255);
        }

        try {
            $cloudflare_token = (new Adapter(new APIToken($api_token)));

            $zone_id = env('CLOUDFLARE_ZONE_ID');
            if(empty($zone_id)){
                $zone_id = (new Zones($cloudflare_token))->getZoneID($zone_name);
            }
            $dns = new DNS($cloudflare_token);
            $record_name = $dns_record_name.'.'.$zone_name;
            $dns_records = $dns->listRecords($zone_id, 'A', $record_name);
            $dns_record_id = (isset($dns_records->result[0]->id) && $dns_records->result[0]->name === $record_name) ? $dns_records->result[0]->id : '';
            $current_value = $target_ip;
            if(!empty($dns_record_id)){
                $current_value = $dns_records->result[0]?->content ?? null;
                $record_id = $dns_records->result[0]?->id ?? null;
                $this->comment(sprintf('record_id: %s', $record_id),'v');
                if(isset($current_value, $record_id)){
                    if($current_value !== $target_ip){
                        $this->info('Updating record');
                        $dns->updateRecordDetails($zone_id,$dns_record_id,['content' => $target_ip, 'type' => 'A', 'name' => $record_name, 'ttl' => 1]);
                    }else{
                        $this->info('Skipping Update, record already exist');
                    }
                }

            }else{
                $dns->addRecord($zone_id,'A',$record_name,$target_ip,1,false);
            }
        }catch (\Throwable $throwable){
            $this->error('Error from Cloudflare API: ' . $throwable->getMessage());
            exit(255);
        }

        render(view('cloudflare.update', [
            'old_ip' => $current_value,
            'new_ip' => $target_ip,
            'record' => $record_name
        ]));
    }

}
