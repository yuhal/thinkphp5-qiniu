<?php
namespace app\index\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
use qiniu\QiniuSdk;
 
class Test extends Command
{
    protected function configure(){
        $this->setName('Test')->setDescription("计划任务 Test");
    }
 
    protected function execute(Input $input, Output $output){
        $output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/
 
        $this->autoChangeImageName('yuhal-picture');
 
        /*** 这里写计划任务列表集 END ***/
        $output->writeln('Date Crontab job end...');
    }
 
    /**
     * 自动变更某个仓库仓库下的图片名称
     * @param string $bucket 仓库名称
     */
    private function autoChangeImageName($bucket){
        $qiniuSdk = new QiniuSdk(config('qiniu.'));
        $buckets = $qiniuSdk->buckets();
        if(in_array($bucket, $buckets[0])){
            $config = array_merge(config('qiniu.'),['bucket'=>$bucket]);
            $qiniuSdk = new QiniuSdk($config);
            $listFiles = $qiniuSdk->listFiles();
            if(!empty($listFiles[0]['items'])){
                $listImages = [];
                foreach ($listFiles[0]['items'] as $key => $value) {
                    if(isImage($value['key'])){
                        $listImages[] = $value;
                    } 
                }
                if(!empty($listImages)){
                    $keys = array_column($listImages, 'key');
                    $keyPairs = array();
                    foreach ($keys as $key) {
                        $suffix = get_extension($key);
                        $keyPairs[$key] = $bucket.'-'.uniqid().'.'.$suffix;
                    }
                    $arguments['bucket'] = $bucket;
                    $arguments['keyPairs'] = $keyPairs;
                    $arguments['destBucket'] = $bucket;
                    $qiniuSdk->buildBatchMove($arguments); 
                }
            }
        }   
    }

}
