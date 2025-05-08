<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Core\Models\MailQueue;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class MailQueuesTest extends AbstractTestCase
{
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['subject' => 'Queued Message']
     */
    public function it_lists_mail_queues(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $record = MailQueue::factory()->create(['subject' => 'Queued Message']);

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(ListMailQueues::class)->);

/** assert */
$component->assertSuccessful()->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Queued Subject', 'to' => 'user@example.com']
     */
    public function it_creates_mail_queue(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['subject' => 'Queued Subject', 'to' => 'user@example.com'];

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(CreateMailQueue::class)->)->fillForm($payload)->call('create');

/** assert */
$component->assertHasNoFormErrors();

        $this->assertDatabaseHas('mail_queues', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => null]
     */
    public function it_fails_to_create_mail_queue_without_subject(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $payload = ['to' => 'fail@example.com'];

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(CreateMailQueue::class)->)->fillForm($payload)->call('create');

/** assert */
$component->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Updated Subject']
     */
    public function it_updates_mail_queue(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $queue = MailQueue::factory()->create(['subject' => 'Initial Subject']);

        $payload = ['subject' => 'Updated Subject'];

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(EditMailQueue::class, ['record' => $queue->id])->)->fillForm($payload)->call('save');

/** assert */
$component->assertHasNoFormErrors();

        $this->assertDatabaseHas('mail_queues', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => null]
     */
    public function it_fails_to_update_mail_queue_with_empty_subject(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $queue = MailQueue::factory()->create(['subject' => 'Valid Subject']);

        $payload = ['subject' => null];

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(EditMailQueue::class, ['record' => $queue->id])->)->fillForm($payload)->call('save');

/** assert */
$component->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_mail_queue(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $queue = MailQueue::factory()->create();

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(ListMailQueues::class)->)->callTableAction('delete', $queue);

        $this->assertDatabaseMissing('mail_queues', ['id' => $queue->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_mail_queue_that_does_not_exist(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $queue = MailQueue::factory()->create();
        $queue->delete();

        /** act */
$component = Livewire::actingAs($this->superAdmin()->test(ListMailQueues::class)->)->callTableAction('delete', $queue);

/** assert */
$component->assertHasErrors();

        $this->assertDatabaseMissing('mail_queues', ['id' => $queue->id]);
    }
}
