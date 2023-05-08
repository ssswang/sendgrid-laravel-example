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
                    "meta" => [] //dynamic template variables
                );

            }
            fclose($handle);
        }
        $helper = new SendgridHelper();
        $helper->init('d-1c10027b9ea344679','sendgrid-key');//dynamic template id and key
        $helper->post($payload, $this->makePayloadDefault());
        
        dump("done");
    }    
    function makePayloadDefault(){
        $lcPayload = [];
        $lcPayload["from_email"] = 'x@x.a';
        $lcPayload["from_name"] = 'School of Learn';
        //add cc|cc|cc, bcc|bcc, reply-to here
        return $lcPayload;
    }
}
