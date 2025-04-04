<?php

namespace GeekCache\Cache;

class IncrementableArrayCache extends ArrayCache implements IncrementableCheckableCache
{
    public function increment($key, $value = 1, $ttl = 0)
    {
        if ($this->shouldDecrement($key, $value)) {
            $modifier = [$this, 'decrementNumber'];
        } elseif ($this->shouldIncrement($value)) {
            $modifier = [$this, 'incrementNumber'];
        } else {
            return false;
        }

        return $this->modify($key, $value, $modifier);
    }

    private function shouldIncrement($value)
    {
        return $value >= 0;
    }

    private function shouldDecrement($key, $value)
    {
        return $value < 0 && $this->has($key);
    }

    private function decrementNumber($current, $value)
    {
        return max(0, $current - abs($value));
    }

    private function incrementNumber($current, $value)
    {
        return is_numeric($current) ? $current + $value : $value;
    }

    private function modify($key, $value, $modifier)
    {
        $current = $this->get($key);
        $newvalue = $modifier($current, $value);
        $this->put($key, $newvalue);
        return $newvalue;
    }
}
