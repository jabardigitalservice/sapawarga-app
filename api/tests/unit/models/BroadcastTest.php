<?php

namespace tests\unit\models;

use app\models\Broadcast;
use Carbon\Carbon;

class BroadcastTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new Broadcast();

        $this->assertFalse($model->validate());

        $model->title       = 'test';
        $model->description = 'test';

        $model->kabkota_id  = 1;
        $model->kec_id      = 1;
        $model->kel_id      = 1;
        $model->rw          = '001';
        $model->status      = 10;

        $this->assertTrue($model->validate());
    }

    public function testValidateRequired()
    {
        $model = new Broadcast();

        $model->title       = null;
        $model->description = null;

        $model->kabkota_id  = null;
        $model->kec_id      = null;
        $model->kel_id      = null;
        $model->rw          = null;
        $model->status      = null;

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('title'));
        $this->assertTrue($model->hasErrors('status'));

        $this->assertFalse($model->hasErrors('description'));
        $this->assertFalse($model->hasErrors('kabkota_id'));
        $this->assertFalse($model->hasErrors('kec_id'));
        $this->assertFalse($model->hasErrors('kel_id'));
        $this->assertFalse($model->hasErrors('rw'));
        $this->assertFalse($model->hasErrors('is_scheduled'));
    }

    public function testScheduledDateTimeShouldRequired()
    {
        $model               = new Broadcast();
        $model->is_scheduled = false;

        $model->validate();

        $this->assertFalse($model->hasErrors('scheduled_datetime'));

        $model               = new Broadcast();
        $model->is_scheduled = true;

        $model->validate();

        $this->assertTrue($model->hasErrors('scheduled_datetime'));
    }

    public function testScheduledDateTimeShouldValidFormat()
    {
        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = (new Carbon())->addDay()->getTimestamp();

        $model->validate();

        $this->assertFalse($model->hasErrors('scheduled_datetime'));

        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = (new Carbon())->addDay()->toDateString();

        $model->validate();

        $this->assertTrue($model->hasErrors('scheduled_datetime'));

        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = (new Carbon())->addDay()->toTimeString();

        $model->validate();

        $this->assertTrue($model->hasErrors('scheduled_datetime'));

        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('scheduled_datetime'));
    }

    public function testScheduledDateTimeShouldAfterNow()
    {
        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = (new Carbon())->subDay()->getTimestamp();

        $model->validate();

        $this->assertTrue($model->hasErrors('scheduled_datetime'));

        $model                     = new Broadcast();
        $model->is_scheduled       = true;
        $model->scheduled_datetime = (new Carbon())->addDay()->getTimestamp();

        $model->validate();

        $this->assertFalse($model->hasErrors('scheduled_datetime'));
    }

    public function testTitleMaximumCharactersShouldSuccess()
    {
        $model        = new Broadcast();
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean m';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testTitleMaximumCharactersShouldFail()
    {
        $model        = new Broadcast();
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean mx';
        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleNotSafeShouldFail()
    {
        $model = new Broadcast();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testDescriptionLongCharactersShouldSuccess()
    {
        $model              = new Broadcast();
        $model->description = file_get_contents(__DIR__ . '/../../data/1000chars.txt');
        $model->validate();

        $this->assertFalse($model->hasErrors('description'));
    }

    public function testDescriptionAllowHTMLTags()
    {
        $model = new Broadcast();

        $model->description = '<p>This is Description</p> <h1>This is Title</h1> <b>Title</b>';

        $model->validate();

        $this->assertFalse($model->hasErrors('description'));
    }

    public function testRWValidation()
    {
        $model       = new Broadcast();
        $model->rw   = '1';
        $model->validate();
        $this->assertTrue($model->hasErrors('rw'));

        $model->rw   = '0001';
        $model->validate();
        $this->assertTrue($model->hasErrors('rw'));

        $model->rw   = '_a1A.';
        $model->validate();
        $this->assertTrue($model->hasErrors('rw'));

        $model->rw   = '001';
        $model->validate();
        $this->assertFalse($model->hasErrors('rw'));
    }

    public function testKabupatenValid()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = null;
        $model->kel_id     = null;
        $model->rw         = null;

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNull($model->kec_id);
        $this->assertNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testKecamatanValid()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = 431;
        $model->kel_id     = null;
        $model->rw         = null;

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNotNull($model->kec_id);
        $this->assertNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testKelurahanValid()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = 431;
        $model->kel_id     = 6093;
        $model->rw         = null;

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNotNull($model->kec_id);
        $this->assertNotNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testRwValid()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = 431;
        $model->kel_id     = 6093;
        $model->rw         = '001';

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNotNull($model->kec_id);
        $this->assertNotNull($model->kel_id);
        $this->assertNotNull($model->rw);
    }

    public function testKecamatanShouldNull()
    {
        $model             = new Broadcast();
        $model->kabkota_id = null;
        $model->kec_id     = 431;
        $model->kel_id     = 6093;
        $model->rw         = '001';

        $model->save(false);

        $this->assertNull($model->kabkota_id);
        $this->assertNull($model->kec_id);
        $this->assertNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testKelurahanShouldNull()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = null;
        $model->kel_id     = 6093;
        $model->rw         = '001';

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNull($model->kec_id);
        $this->assertNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testRwShouldNull()
    {
        $model             = new Broadcast();
        $model->kabkota_id = 22;
        $model->kec_id     = 431;
        $model->kel_id     = null;
        $model->rw         = '001';

        $model->save(false);

        $this->assertNotNull($model->kabkota_id);
        $this->assertNotNull($model->kec_id);
        $this->assertNull($model->kel_id);
        $this->assertNull($model->rw);
    }

    public function testScheduledDateDue()
    {
        $model = new Broadcast();
        $model->scheduled_datetime = time() + 3600;

        $this->assertFalse($model->isDue());

        $model = new Broadcast();
        $model->scheduled_datetime = time() - 3600;

        $this->assertTrue($model->isDue());
    }
}
