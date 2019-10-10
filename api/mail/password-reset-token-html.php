<?php
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $user app\models\User */
    /* @var $appName string */
    /* @var $resetURL string */

?>
<table border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #FFFFFF;">
    <tbody>
    <tr>
        <td valign="top" class="mcnTextContent" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: left; padding: 36px; word-break: break-word;">
            <div style="text-align: left; word-wrap: break-word;">Permintaan Reset Password Akun Sapawarga (<?= Html::encode($user->username) ?>)<br />
                <br />
                <br />Silahkan klik link berikut ini untuk membuat password baru Anda:
                <br /><br />
                <a href="<?=Html::encode($resetURL);?>"><?=$resetURL;?></a>
            </div>
        </td>
    </tr>
    </tbody>
</table>
