<?php
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $user app\models\User */
    /* @var $appName string */
    /* @var $email string */
    /* @var $phone string */
    /* @var $address string */
    /* @var $confirmURL string */

?>
<table border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #FFFFFF;">
    <tbody>
    <tr>
        <td valign="top" class="mcnTextContent" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: left; padding: 36px; word-break: break-word;">
            <div style="text-align: left; word-wrap: break-word;">

                <br />Terima kasih telah melakukan update profil Anda pada aplikasi Sapawarga. Berikut ini data yang telah Anda update:

                <br />
                <br />Nama: <?=$name;?>
                <br />Email: <?=$email;?>
                <br />Nomor Telepon: <?=$phone;?>
                <br />Alamat: <?=$address;?>

                <br />
                <br />Silahkan klik link berikut ini untuk melakukan verifikasi pada akun Sapawarga:
                <br /><a href="<?=Html::encode($confirmURL);?>"><?=$confirmURL;?></a>
            </div>
        </td>
    </tr>
    </tbody>
</table>
