<?php

namespace App\Notifications;

use App\Models\Fixture;
use App\Models\TicketSalesPhase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketSalesPhase extends Notification implements ShouldQueue
{
    use Queueable;

    protected Fixture $fixture;
    protected TicketSalesPhase $salesPhase;

    /**
     * Create a new notification instance.
     */
    public function __construct(Fixture $fixture, TicketSalesPhase $salesPhase)
    {
        $this->fixture = $fixture;
        $this->salesPhase = $salesPhase;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'twilio'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $teamName = $this->fixture->team;
        $salesPhaseName = $this->salesPhase->sales_phase;
        $ticketUrl = $this->fixture->ticket_url ?? $this->fixture->arsenal_url;
        $matchDate = $this->fixture->date->format('l, j F Y - g:i A');
        
        $saleDate = 'Unknown';
        if ($this->salesPhase->sale_date) {
            $saleDate = $this->salesPhase->sale_date->format('l, j F Y');
            if ($this->salesPhase->sale_time) {
                $saleDate .= ' at ' . $this->salesPhase->sale_time;
            }
        }
        
        $message = (new MailMessage)
            ->subject("New Arsenal Away Ticket Sales Phase: {$teamName} - {$salesPhaseName}")
            ->line("A new ticket sales phase is now available for the Arsenal away match against {$teamName}.")
            ->line("Match Date: {$matchDate}")
            ->line("Sales Phase: {$salesPhaseName}");
            
        if ($this->salesPhase->who_can_buy) {
            $message->line("Who Can Buy: {$this->salesPhase->who_can_buy}");
        }
        
        if ($this->salesPhase->points_required) {
            $message->line("Points Required: {$this->salesPhase->points_required}");
        }
        
        $message->line("Sales Start: {$saleDate}")
            ->action('View Ticket Information', $ticketUrl)
            ->line('Hurry, away tickets sell out quickly!');
            
        return $message;
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): string
    {
        $teamName = $this->fixture->team;
        $salesPhaseName = $this->salesPhase->sales_phase;
        $matchDate = $this->fixture->date->format('l, j F Y - g:i A');
        
        $saleDate = 'Unknown';
        if ($this->salesPhase->sale_date) {
            $saleDate = $this->salesPhase->sale_date->format('l, j F Y');
            if ($this->salesPhase->sale_time) {
                $saleDate .= ' at ' . $this->salesPhase->sale_time;
            }
        }

        $message = "New Arsenal Away Ticket Sales Phase: {$teamName} - {$salesPhaseName}\n";
        $message .= "Match Date: {$matchDate}\n";
        $message .= "Sales Start: {$saleDate}\n";
        
        if ($this->salesPhase->who_can_buy) {
            $message .= "Who Can Buy: {$this->salesPhase->who_can_buy}\n";
        }
        
        if ($this->salesPhase->points_required) {
            $message .= "Points Required: {$this->salesPhase->points_required}\n";
        }
        
        $message .= "Hurry, away tickets sell out quickly!";
        
        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'fixture_id' => $this->fixture->id,
            'fixture_team' => $this->fixture->team,
            'fixture_date' => $this->fixture->date->toDateTimeString(),
            'sales_phase_id' => $this->salesPhase->id,
            'sales_phase_name' => $this->salesPhase->sales_phase,
        ];
    }
}
