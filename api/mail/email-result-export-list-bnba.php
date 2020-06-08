<?php
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $user app\models\User */
    /* @var $final_url string */

?>
<table border="0" cellpadding="18" cellspacing="0" class="mcnTextContentContainer" width="100%" style="background-color: #FFFFFF;">
    <tbody>
    <tr>
        <td valign="top" class="mcnTextContent" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; text-align: left; padding: 36px; word-break: break-word;">
            <div style="text-align: left; word-wrap: break-word;">

                <br />Terima kasih telah menggunakan aplikasi Sapawarga. Hasil export list BNBA yang anda minta telah kami selesaikan. Silahkan mengunduh file list BNBA melalui link berikut:
                <br />
                <br /><a href="<?=Html::encode($final_url);?>" target="_blank"><?=Html::encode($final_url);?></a>
            </div>
        </td>
    </tr>
    </tbody>
</table>
