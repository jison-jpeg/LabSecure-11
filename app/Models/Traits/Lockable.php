<?php

namespace App\Models\Traits;

use App\Models\Lock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait Lockable
{
    /**
     * Applies a lock on the current model instance for editing by a given user.
     *
     * @param int|null $userId The user who is locking the record. Defaults to the currently authenticated user.
     * @param int $durationInMinutes How long the lock should last. Defaults to 5 minutes.
     * @return void
     */
    public function applyLock($userId = null, $durationInMinutes = 5)
    {
        $userId = $userId ?? Auth::id();

        // Remove any existing lock first
        $this->releaseLock();

        Lock::create([
            'lockable_type' => static::class,
            'lockable_id' => $this->getKey(),
            'locked_by' => $userId,
            'lock_expires_at' => Carbon::now()->addMinutes($durationInMinutes),
        ]);
    }

    /**
     * Releases the lock on the current model instance.
     *
     * @return void
     */
    public function releaseLock()
    {
        Lock::where('lockable_type', static::class)
            ->where('lockable_id', $this->getKey())
            ->delete();
    }

    /**
     * Checks if the current model instance is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        $lock = Lock::where('lockable_type', static::class)
            ->where('lockable_id', $this->getKey())
            ->first();

        return $lock && $lock->lock_expires_at && $lock->lock_expires_at->isFuture();
    }

    /**
     * Checks if the current model instance is locked by a specific user.
     *
     * @param int $userId
     * @return bool
     */
    public function isLockedBy($userId)
    {
        $lock = Lock::where('lockable_type', static::class)
            ->where('lockable_id', $this->getKey())
            ->first();

        return $lock && $lock->locked_by == $userId && $lock->lock_expires_at->isFuture();
    }

    /**
     * Get the ID of the user who currently holds the lock.
     *
     * @return int|null
     */
    public function currentLockUserId()
    {
        $lock = Lock::where('lockable_type', static::class)
            ->where('lockable_id', $this->getKey())
            ->first();

        return $lock ? $lock->locked_by : null;
    }

    /**
     * Get the user who currently holds the lock along with the relative time since it was locked.
     *
     * @return array|null
     */
    public function lockDetails()
    {
        $lock = Lock::where('lockable_type', static::class)
            ->where('lockable_id', $this->getKey())
            ->first();

        if (!$lock || !$lock->lock_expires_at->isFuture()) {
            return null;
        }

        $user = User::find($lock->locked_by);
        $timeAgo = Carbon::parse($lock->created_at)->diffForHumans();

        return [
            'user' => $user,
            'timeAgo' => $timeAgo,
        ];
    }
}
