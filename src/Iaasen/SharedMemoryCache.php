<?php
/**
 * User: ingvar.aasen
 * Date: 20.05.2025
 */

namespace Iaasen;

readonly class SharedMemoryCache
{
    public function __construct(
        private int $key,
        private int $varKey = 1,
        private int $size = 8192
    ) {}

    public function store(mixed $data, ?int $ttlSeconds = null): void
    {
        $sem = sem_get($this->key);
        if ($sem && sem_acquire($sem)) {
            $shm = shm_attach($this->key, $this->size);
            if ($shm) {
                $payload = [
                    'data' => $data,
                    'expires_at' => is_int($ttlSeconds) ? time() + $ttlSeconds : null,
                ];
                shm_put_var($shm, $this->varKey, $payload);
                shm_detach($shm);
            }
            sem_release($sem);
        }
    }

    /**
     * @return mixed Returns null if the cached item is not found
     */
    public function fetch(): mixed
    {
        $sem = sem_get($this->key);
        if ($sem && sem_acquire($sem)) {
            $shm = shm_attach($this->key, $this->size);
            $result = null;
            if ($shm && shm_has_var($shm, $this->varKey)) {
                $payload = shm_get_var($shm, $this->varKey);
                if (
                    is_array($payload) &&
                    array_key_exists('data', $payload) &&
                    (empty($payload['expires_at']) || $payload['expires_at'] > time())
                ) {
                    $result = $payload['data'];
                } else {
                    // Expired — remove it
                    shm_remove_var($shm, $this->varKey);
                }
            }
            if ($shm) {
                shm_detach($shm);
            }
            sem_release($sem);
            return $result;
        }
        return null;
    }

    public function clear(): void
    {
        $sem = sem_get($this->key);
        if ($sem && sem_acquire($sem)) {
            $shm = shm_attach($this->key, $this->size);
            if ($shm && shm_has_var($shm, $this->varKey)) {
                shm_remove_var($shm, $this->varKey);
                shm_detach($shm);
            }
            sem_release($sem);
        }
    }

}
