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
        /* arrange */
        $this->markTestIncomplete();
        $record = MailQueue::factory()->create(['subject' => 'Queued Message']);

        Livewire::test(ListMailQueues::class)
            ->actingAs($this->superAdmin())
            ->assertSuccessful()
            ->assertSeeDatabaseRecords($record);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Queued Subject', 'to' => 'user@example.com']
     */
    public function it_creates_mail_queue(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $payload = ['subject' => 'Queued Subject', 'to' => 'user@example.com'];

        Livewire::test(CreateMailQueue::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mail_queues', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => null]
     */
    public function it_fails_to_create_mail_queue_without_subject(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $payload = ['to' => 'fail@example.com'];

        Livewire::test(CreateMailQueue::class)
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => 'Updated Subject']
     */
    public function it_updates_mail_queue(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $queue = MailQueue::factory()->create(['subject' => 'Initial Subject']);

        $payload = ['subject' => 'Updated Subject'];

        Livewire::test(EditMailQueue::class, ['record' => $queue->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mail_queues', $payload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload ['subject' => null]
     */
    public function it_fails_to_update_mail_queue_with_empty_subject(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $queue = MailQueue::factory()->create(['subject' => 'Valid Subject']);

        $payload = ['subject' => null];

        Livewire::test(EditMailQueue::class, ['record' => $queue->id])
            ->actingAs($this->superAdmin())
            ->fillForm($payload)
            ->call('save')
            ->assertHasFormErrors(['subject']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_deletes_mail_queue(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $queue = MailQueue::factory()->create();

        Livewire::test(ListMailQueues::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $queue);

        $this->assertDatabaseMissing('mail_queues', ['id' => $queue->id]);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload []
     */
    public function it_fails_to_delete_mail_queue_that_does_not_exist(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $queue = MailQueue::factory()->create();
        $queue->delete();

        Livewire::test(ListMailQueues::class)
            ->actingAs($this->superAdmin())
            ->callTableAction('delete', $queue)
            ->assertHasErrors();

        $this->assertDatabaseMissing('mail_queues', ['id' => $queue->id]);
    }
}
