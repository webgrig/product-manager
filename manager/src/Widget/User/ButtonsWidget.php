<?php

declare(strict_types=1);

namespace App\Widget\User;

use App\ReadModel\User\NetworkFetcher;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ButtonsWidget extends AbstractExtension
{
    private $connection;
    private $security;

    public function __construct(Connection $connection, Security $security)
    {
        $this->connection = $connection;
        $this->security = $security;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('socialButtons', [$this, 'missingNetworks'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function missingNetworks(Environment $twig): string
    {
        $user = $this->security->getUser();
        $networkFetcher = new NetworkFetcher($this->connection);
        $networks = $networkFetcher->getNotMembershipNetworks($user->getId());
        foreach ($networks as $network)
        {
            $network->id = $network->network;
            $network->path ='profile.oauth.' . $network->network;
            $network->title = ucwords('Attach ' . $network->network);
        }
        return $twig->render('widget/user/buttons.html.twig', [
            'networks' => $networks
        ]);
    }
}
