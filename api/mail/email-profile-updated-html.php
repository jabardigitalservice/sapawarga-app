<?php
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $user app\models\User */
    /* @var $appName string */
    /* @var $email string */
    /* @var $appName string */
    /* @var $phone string */
    /* @var $address string */
    /* @var $confirmURL string */

?>
<table border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #FFFFFF;">
    <tbody>
    <tr>
        <td valign="top" class="mcnTextContent" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: left; padding: 36px; word-break: break-word;">
            <div style="text-align: center; margin-bottom: 36px">
                <?=$appName;?>
            </div>
            <div style="text-align: left; word-wrap: break-word;">

                <br />Terima kasih telah melakukan update profil anda pada aplikasi Sapawarga. Berikut ini data yang telah anda update:

                <br />
                <br />Nama : <?=$name;?>
                <br />Email: <?=$email;?>
                <br />Nomor telepon: <?=$phone;?>
                <br />Alamat: <?=$address;?>

                <br />
                <br />Silahkan klik link berikut ini untuk melakukan verifikasi pada akun Sapawarga Link Verifikasi
                <br /><a href="<?=Html::encode($confirmURL);?>"><?=$confirmURL;?></a>

                <br />
                <br />Terima kasih,
                <br />Admin Sapawarga
                <br />No. Telp: 081212124023
                <br />Email: <a href = "mailto: sapawarga@jabarprov.go.id">sapawarga@jabarprov.go.id</a>

                <div class="footer" style="font-size: 0.7em; padding: 0px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: right; color: #777777; line-height: 14px; margin-top: 36px;">© <?=date("Y");?> Company
                    <br>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>