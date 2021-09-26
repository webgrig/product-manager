<?php

declare(strict_types=1);

namespace App\Widget\Work\Members\Groups;

use App\ReadModel\Work\Members\Member\MemberFetcher;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NoMembersGroup extends AbstractExtension
{

    private MemberFetcher $fetcher;

    public function __construct(MemberFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('deleteButton', [$this, 'noMembersGroup'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function noMembersGroup(Environment $twig, string $groupId): string
    {
        $noMembersGroup = !$this->fetcher->hasByGroup($groupId);
        return $twig->render('widget/work/members/groups/deleteButton.html.twig', [
            'noMembersGroup' => $noMembersGroup,
            'groupId' => $groupId
        ]);
    }
}
