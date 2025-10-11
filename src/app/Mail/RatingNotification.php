<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RatingNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * @var User
     */
    public $ratedUser; // 評価されたユーザー

    /**
     * @var User
     */
    public $raterUser; // 評価したユーザー

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction, User $ratedUser, User $raterUser)
    {
        $this->transaction = $transaction;
        $this->ratedUser = $ratedUser;
        $this->raterUser = $raterUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // メールの件名とビューを設定
        return $this->subject('【フリマアプリ】取引相手から評価が届きました')
                    ->view('emails.rating_notification');
    }
}
