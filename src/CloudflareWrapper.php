<?php
namespace App;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\User;
use Cloudflare\API\Endpoints\Zones;
use Exception;

class CloudflareWrapper
{
    private $token = '';
    private $zone_id = '';
    private $zone_name = '';
    private $cf_api;
    private $record_name = '';
    private $record_ip = '';
    

    /**
     * Execute zone update
     */
    public function run(){
        try {
            $this->loadcli();
            $this->editDNSRecord($this->record_name,$this->record_ip);
            echo "$this->record_name.$this->zone_name\tIN\tA\t$this->record_ip\n";
        }catch (Exception $ex){
            echo $ex->getMessage();
            exit(1);
        }
        exit(0);
    }


    /**
     * @param string $name
     * @param string $value
     * @return mixed
     */
    private function editDNSRecord($name = '', $value = ''){
        $dns = new DNS($this->cf_api);
        $record_name = $name.'.'.$this->zone_name;
        $dns_records = $dns->listRecords($this->zone_id, 'A', $record_name);
        $dns_record_id = (isset($dns_records->result[0]->id) && $dns_records->result[0]->name === $record_name) ? $dns_records->result[0]->id : '';
        if(!empty($dns_record_id)){
            if($dns_records->result[0]->content !== $value){
                return $dns->updateRecordDetails($this->zone_id,$dns_record_id,['content' => $value, 'type' => 'A', 'name' => $record_name, 'ttl' => 1]);
            }else{
                return false;
            }
        }else{
            return $dns->addRecord($this->zone_id,'A',$name,$value,1,false);
        }
    }

    /**
     * init
     */
    public function __construct(){
        try {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__.'/../');
            $dotenv->load();
            $dotenv->required(['CLOUDFLARE_TOKEN']);
            $this->token = $_ENV['CLOUDFLARE_TOKEN'];
            $this->record_name = $_ENV['DNS_RECORD_NAME'];
            $key = new APIToken($this->token);
            $this->cf_api = new Guzzle($key);
        }catch (Exception $ex){
            echo $ex->getMessage();
            exit(1);
        }
    }

    /**
     * Run cli functions
     * @throws Exception $ex
     * @return void
     */
    private function loadcli(): void{
        if(!$this->checkRequiredEnv()){
            throw new Exception("Error empty required CLOUDFLARE_TOKEN");
        }
        $this->loadZoneID();

        if(isset($argv[1]) && !empty($argv[1])){
            $this->record_name = $argv[1];
        }

        if(empty($this->record_name)){
            throw new Exception( "empty DNS_RECORD_NAME or record_name");
        }
        
        if(isset($argv[2]) && !empty($argv[2])){
            $this->record_ip = $argv[2];
        }else{
            $this->record_ip = PublicIP::get();    
        }

        if(empty($this->record_ip) || filter_var($this->record_ip, FILTER_VALIDATE_IP) === false){
            throw new Exception("invalid record_ip");
        }
    }

    /**
     * Load cloudflare zone_id from .env zone_name or zone_id
     * @throws Exception
     * @return void
     */
    private function loadZoneID(): void{
        /**
         * TODO: Load zone_name/zone_id from cli parameter
         */
        $zone_id = "";
        if(isset($_ENV['CLOUDFLARE_ZONE_NAME']) && !empty($_ENV['CLOUDFLARE_ZONE_NAME']) && filter_var($_ENV['CLOUDFLARE_ZONE_NAME'],FILTER_VALIDATE_DOMAIN) !== false){
            $zone_id  = (new Zones($this->cf_api))->getZoneID($_ENV['CLOUDFLARE_ZONE_NAME']);
        }elseif(isset($_ENV['CLOUDFLARE_ZONE_ID']) && !empty($_ENV['CLOUDFLARE_ZONE_ID'])){
            $zone_id = $_ENV['CLOUDFLARE_ZONE_ID'];
        }
        $zone_details  = (new Zones($this->cf_api))->getZoneById($zone_id);
        $this->zone_id = $zone_details->result->id ?? '';
        $this->zone_name = $zone_details->result->name ?? '';
        if(empty($this->zone_id)){
            throw new Exception('invalid CLOUDFLARE_ZONE_ID or CLOUDFLARE_ZONE_NAME');
        }
    }

    /**
     * Check if required ENV var are defined e properly loaded
     * @return bool
     */
    private function checkRequiredEnv(): bool{
        if(empty($this->token)){
            return false;
        }else{
            return true;
        }
    }
}