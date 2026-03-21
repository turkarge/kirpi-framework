<?php

declare(strict_types=1);

namespace Core\Frontend\Tabler;

final class LayoutParts
{
    public function pageHeader(string $title, string $subtitle): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeSubtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <!-- BEGIN PAGE HEADER -->
        <div class="page-header d-print-none" aria-label="Page header">
          <div class="container-xl">
            <div class="row g-2 align-items-center">
              <div class="col">
                <div class="page-pretitle">Kirpi Framework</div>
                <h2 class="page-title">{$safeTitle}</h2>
                <div class="text-secondary mt-1">{$safeSubtitle}</div>
              </div>
            </div>
          </div>
        </div>
        <!-- END PAGE HEADER -->
HTML;
    }

    public function pageBody(string $content): string
    {
        return <<<HTML
        <!-- BEGIN PAGE BODY -->
        <div class="page-body">
          <div class="container-xl">
            {$content}
          </div>
        </div>
        <!-- END PAGE BODY -->
HTML;
    }

    public function footer(): string
    {
        return <<<'HTML'
        <!--  BEGIN FOOTER  -->
        <footer class="footer footer-transparent d-print-none">
          <div class="container-xl">
            <div class="row text-center align-items-center">
              <div class="col-12">
                <ul class="list-inline mb-0">
                  <li class="list-inline-item">
                    Copyright &copy; 2026
                    <a href="/kirpi/admin-demo" class="link-secondary">Kirpi Framework</a>. All rights reserved.
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </footer>
        <!--  END FOOTER  -->
HTML;
    }
}

