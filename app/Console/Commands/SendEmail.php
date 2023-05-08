<?php
namespace App\Console\Commands;

use App\Console\Commands\SendgridHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendEmail extends Command
{
    protected $signature = 'send-email';

    protected $description = 'Sendgrid';


    function handle()
    {
        dump($this->signature . " starts");
        
        if (($handle = fopen("insts.csv", "r")) !== FALSE) {
            while ($data = fgetcsv($handle)) {
                $payload [] = array(
                    "recipient" => $data[0],
                    "reference" => $data[0],
                    "meta" => []
                );

            }
            fclose($handle);
        }
        $helper = new SendgridHelper();
        $helper->init('d-1c10027b9ea344679','sendgrid-key');
        $helper->post($payload, $this->makePayloadDefault());
        
        dump("done");
    }    
    function makePayloadDefault(){
        $lcPayload = [];
        $lcPayload["from_email"] = 'x@x.a';
        $lcPayload["from_name"] = 'School of Learn';
        //cc, bcc, reply-to
        return $lcPayload;
    }
}