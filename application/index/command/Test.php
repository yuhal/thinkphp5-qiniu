<?php
namespace app\index\command;

use think\Request;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Qiniu\Auth;
use Qiniu\Http\Client;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
use app\index\controller\Mail;

class Test extends Command
{

    private $name = '批量重命名文件';

    /**
     * 配置
     * @param Request $request Request对象
     */
    protected function configure()
    {
        (new Mail)->sendEmailReminders('hello','hello');exit;
        $this->setName('Test')
            ->addArgument('bucket', Argument::OPTIONAL, "资源空间名", config('qiniu.bucket'))
            ->setDescription('
                批量重命名某个空间下的文件，
                资源空间名为空则使用默认资源空间，
                命名格式为：空间名+uniqid()+文件类型后缀;
            ');
    }
 
    /**
     * 执行
     * @param Input $input Input对象
     * @param Output $output Output对象
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln($this->name.'开始');
        $auth = new Auth(config('qiniu.accessKey'), config('qiniu.secretKey'));
        $bucketMgr = new BucketManager($auth);
        $bucket = trim($input->getArgument('bucket'));
        $buckets = $bucketMgr->buckets(config('qiniu.shared'));
        if (in_array($bucket, $buckets[0])) {
            $listFiles = $bucketMgr->listFiles($bucket);
            if (!empty($listFiles[0]['items'])) {
                $listExt = [];
                foreach ($listFiles[0]['items'] as $key => $value) {
                    if (config('qiniu.mimeType') == $value['mimeType']) {
                        $listExt[] = $value;
                    }
                }
                $hadChangeList = $bucketMgr->listFiles($bucket,$bucket);
                if (!empty($hadChangeList[0]['items'])) {
                    $hadChangeListImages = [];
                    foreach ($hadChangeList[0]['items'] as $key => $value) {
                        if (config('qiniu.mimeType') == $value['mimeType']) {
                            $hadChangeListImages[] = $value;
                        }
                    }
                    $listExt = get_diff_array_by_filter($listExt, $hadChangeListImages);
                }
                if (!empty($listExt)) {
                    $keys = array_column($listExt, 'key');
                    $keyPairs = array();
                    foreach ($keys as $key) {
                        $suffix = get_extension($key);
                        $keyPairs[$key] = $bucket.'-'.uniqid().'.'.strtolower($suffix);
                    }
                    $ops = $bucketMgr->buildBatchMove($bucket, $keyPairs, $bucket, true);
                    list($ret, $err) = $bucketMgr->batch($ops);
                    if ($err) {
                        $output->writeln($this->name.'失败');
                    }else{
                        $output->writeln($this->name.'成功');
                    }
                }
            }
        }
        $output->writeln($this->name.'结束');
    }
}
