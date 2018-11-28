<?php
namespace Grav\Plugin\LabelTree;

use \Grav\Common\Grav;
use \Grav\Common\Page\Page;

class Label
{
    /** @var string */
    public $name = '';
    /** @var label[] */
    public $children = [];
    /** @var string[] */
    public $pagePaths = [];

    public function __construct(string $name = '', Page $page = null)
    {
        $this->name = $name;

        if ($page) {
            $this->pagePaths[] = $page->path();
        }
     }
}
