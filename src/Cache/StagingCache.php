<?php
namespace GeekCache\Cache;

class StagingCache
{
    private array $stagedRequests = [];
    private array $stagedResults = [];
    
    public function stage(string $key): void
    {
        if ($this->resultIsStaged($key)) {
            $this->stagedResults[$key]['remainingReads']++;
        } else {
            $this->stagedRequests[$key] = ($this->stagedRequests[$key] ?? null) ? $this->stagedRequests[$key] + 1 : 1;
        }
    }
    
    //fully unstage all counts for key
    public function unstage(string $key): void
    {
        unset($this->stagedRequests[$key]);
        unset($this->stagedResults[$key]);
    }
    
    public function resultIsStaged(string $key): bool
    {
        return array_key_exists($key, $this->stagedResults);
    }

    public function updateResultIfStaged(string $key, $result)
    {
        if ($this->resultIsStaged($key)) {
            $this->stagedResults[$key]['value'] = $result;
        }
    }
    
    public function deleteResultIfStaged(string $key)
    {
        if ($this->resultIsStaged($key)) {
            $this->stagedResults[$key]['value'] = false;
        }
    }

    public function readResult($key)
    {
        $result = $this->stagedResults[$key]['value'];
        $this->stagedResults[$key]['remainingReads']--;
        if (!$this->stagedResults[$key]['remainingReads']) {
            unset($this->stagedResults[$key]);
        }
        return $result;
    }
    
    // the key is for the primary item for the multiGet. This item may or may  not be staged
    // for request, so we have to handle it separately
    public function stageResults($key, array $results)
    {
        // if the result we are getting was also staged, we have to handle it, because
        // it will not be later read
        if (array_key_exists($key, $this->stagedRequests)) {
            $this->stagedRequests[$key]--;
            if ($this->stagedRequests[$key] <= 0) {
                unset($this->stagedRequests[$key]);
            }
        }

        foreach ($this->stagedRequests as $key => $stageCount) {
            $this->stagedResults[$key] = [
                'value' => $results[$key] ?? false,
                'remainingReads' => $stageCount
            ];
        }
        $this->stagedRequests = [];
    }
    
    public function getStagedRequests()
    {
        return array_keys($this->stagedRequests);
    }
    
    
    public function anyRequestsStaged()
    {
        return count($this->stagedRequests) > 0;
    }
    
    public function getStagedRequestsCount(): int
    {
        return count($this->stagedRequests);
    }
    public function getStagedResultsCount(): int
    {
        return count($this->stagedResults);
    }

}
