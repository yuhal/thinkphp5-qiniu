<?php
namespace app\index\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Cache;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail
{

    /**
     * 构造方法
     */
    public function __construct(){
        $this->PHPMailer = new PHPMailer(true);
    }

    /**
     * 发送邮件提醒
     * @param string $email 收件人邮箱地址
     * @param string $name 收件人邮箱地址，收件人名称
     * @param string $Subject 邮件主题
     * @param string $Body 邮件内容
     * @return \think\Response
     */
    public function sendEmailReminders($Subject,$Body)
    {
        try {
            //服务器设置
            $this->PHPMailer->SMTPDebug = 2;                                       // 启用详细调试输出
            $this->PHPMailer->isSMTP();                                            // 将mailer设置为使用SMTP
            $this->PHPMailer->Host       = config('mail.Host');  // 指定主服务器和备份SMTP服务器
            $this->PHPMailer->SMTPAuth   = config('mail.SMTPAuth');                                 // 使SMTP认证
            $this->PHPMailer->Username   = config('mail.Username');                 // SMTP用户名
            $this->PHPMailer->Password   = config('mail.Password');                             // SMTP密码
            $this->PHPMailer->SMTPSecure = config('mail.SMTPSecure');                             // 启用TLS加密，' ssl '也接受
            $this->PHPMailer->Port       = config('mail.Port');                                    // 要连接到的TCP端口

            //收件人mail
            $this->PHPMailer->setFrom(config('mail.Username'), config('.Sender'));  // 寄送人邮箱地址，寄送人名称
            $this->PHPMailer->addAddress(config('mail.Email'), config('mail.Receiver'));     // 收件人邮箱地址，收件人名称
            // $this->PHPMailer->addAddress(config('mail.Username'));               // 名称是可选的
            // $this->PHPMailer->addReplyTo(config('mail.Username'), 'Information');    
            // $this->PHPMailer->addCC(config('mail.Username'));   //抄送
            // $this->PHPMailer->addBCC(config('mail.Username'));  //密件抄送，密件副本

            // 附件
            // $this->PHPMailer->addAttachment('/var/tmp/file.tar.gz');         // 添加附件
            // $this->PHPMailer->addAttachment('/tmp/image.jpg', 'new.jpg');    // 可选的名字

            // 内容
            // $this->PHPMailer->isHTML(true);                                  // 设置电子邮件格式为HTML
            $this->PHPMailer->Subject = $Subject;
            $this->PHPMailer->Body    = $Body;
            // $this->PHPMailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $this->PHPMailer->send();

            return true;
        } catch (Exception $e) {
            return $this->PHPMailer->ErrorInfo;
        }
    }

}