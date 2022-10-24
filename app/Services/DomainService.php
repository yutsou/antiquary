<?php

namespace App\Services;

use App\Repositories\DomainRepository;

class DomainService extends DomainRepository
{
    public function createDomain($userId, $domain)
    {
        $input['user_id'] = $userId;
        $input['domain'] = $domain;
        DomainRepository::create($input);
    }

    public function createDomains($userId, $domains)
    {
        foreach ($domains as $domain) {
            DomainRepository::create(['user_id'=> $userId, 'domain' => $domain]);
        }
    }

    public function deletedomain($domainId)
    {
        DomainRepository::delete($domainId);
    }

    public function auctioneerUpdateDomain($user, $newDomains)
    {
        if(!isset($newDomains)) {
            $newDomains = [];
        }
        $existDomains = $user->domains->pluck('domain')->toArray();

        $userId = $user->id;

        $createList = array_diff($newDomains, $existDomains);
        foreach($createList as $createItem)
        {
            $this->createDomain($userId, $createItem);
        }
        $deleteList = array_diff($existDomains, $newDomains);
        foreach($deleteList as $deleteItem)
        {
            $domainId = DomainRepository::getDomainByUserIdAndDomainId($userId, $deleteItem)->id;
            $this->deleteDomain($domainId);
        }
    }

    public function expertGetDomains($user)
    {
        $domains = $user->domains;
        return $domains;
    }

    public function expertUpdatedomain($request, $domainId)
    {
        $input = $request->all();
        DomainRepository::fill($input, $domainId);
    }

    public function getDomain($domainId)
    {
        $domain = domainRepository::find($domainId);
        return $domain;
    }
}
