<?php

declare(strict_types=1);

namespace App\Widget\Work\Projects\Project\Settings\Department;

use App\ReadModel\Work\Projects\Project\DepartmentFetcher;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NoMembersDepartment extends AbstractExtension
{

    private DepartmentFetcher $fetcher;

    public function __construct(DepartmentFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }
    public function getFunctions(): array
    {
        return [
            new TwigFunction('deleteDepartmentButton', [$this, 'noMembersDepartment'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function noMembersDepartment(Environment $twig, string $projectId, string $departmentId): string
    {
        $noMembersDepartment = !$this->fetcher->hasByMembers($departmentId);
        return $twig->render('widget/work/projects/project/deleteDepartmentButton.html.twig', [
            'noMembersDepartment' => $noMembersDepartment,
            'projectId' => $projectId,
            'departmentId' => $departmentId
        ]);
    }
}
