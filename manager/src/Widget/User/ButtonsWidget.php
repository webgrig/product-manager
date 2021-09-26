<?php

declare(strict_types=1);

namespace App\Widget\User;

use App\Model\User\Service\FetchModeService;
use App\ReadModel\User\NetworkFetcher;
use App\ReadModel\User\NetworkView;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ButtonsWidget extends AbstractExtension
{
    private $fetcher;

    public function __construct(Connection $connection, NetworkFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('socialButtons', [$this, 'missingNetworks'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function missingNetworks(Environment $twig, string $userId): string
    {
        $networks = $this->fetcher->getAnotherNetworks($userId);

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
