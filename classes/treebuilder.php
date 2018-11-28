<?php
namespace Grav\Plugin\LabelTree;

use \Grav\Common\Grav;
use \Grav\Common\Page\Collection;
use \Grav\Common\Page\Page;
use \Grav\Common\Page\Pages;

use \Grav\Plugin\LabelTree\Label;

class TreeBuilder
{
    /** @var Label|null Contains a tree of Labels */
    protected $labelTree = null;

    /**
     * Return tree of labels to be displayed by template.
     *
     * @return Label The root node of a tree of labels.
     */
    public function getLabelTree()
    {
        return $this->labelTree ?: $this->buildLabelTree();
    }

    /**
     * Get pages filtered by $param containing labels.
     *
     * @param $param Query parameter used to access blog page. E.g. http://mydomain/blog/labels:label1,label2
     *
     * @return Collection
     */
    public function getPages(string $param = ''): Collection
    {
        // Return collection of current blog page if no query parameters are provided.
        if ($param === '') {
            return Grav::instance()['page']->collection();
        }

        // Create labelTree if not yet created.
        if (!$this->labelTree) {
            $this->buildLabelTree();
        }

        // Walk down the labelTree until node with correct label is found.
        $labelNode = $this->labelTree;
        $labels = explode(',', $param);
        foreach($labels as $label) {
            $labelNode = $labelNode->children[$label];
        }

        // Create collection of pages using paths in Label node
        $pages = Grav::instance()['pages']->all();
        $collection = new Collection();
        foreach($labelNode->pagePaths as $path) {
            $collection->addPage($pages[$path]);
        }

        return $collection;
    }

    /**
     * Build tree of labels using acollection of all pages.
     *
     */
    protected function buildLabelTree(): Label
    {
        $cache = Grav::instance()['cache'];

        $id = hash('md5', serialize('labeltree'));
        $cached = $cache->fetch($id);
        $this->labelTree = $cached ? $cached['labeltree'] : null;

        if ($this->labelTree instanceof Label) {
            return $this->labelTree;
        }

        // Get all pages in the site
        $pages = Grav::instance()['pages'];

        $this->labelTree = new Label('root');

        // Loop through all pages
        foreach ($pages->all() as $page) {
            if (!isset($page->header()->labels)) {
                continue;
            }

            $labels = $page->header()->labels;
            if ($labels) {
                // Convert string labels found in frontmatter of page to Label class
                $pageLabels = new Label();
                $this->convertLabels($pageLabels, $labels, $page);

                // Merge labes found in page into labelTree
                $this->mergeTree($this->labelTree, $pageLabels->children);
            }
        }

        // Store tree in cache for reuse
        $cache->save($id, ['labeltree' => $this->labelTree]);

        return $this->labelTree;
    }

    /**
     * Convert PHP arrays tructure based on Yaml into a Label hierarchy.
     *
     * @param Label $labelTree The resulting converted Label tree
     * @param array $labels PHP array based on Page::header['labels']
     * @param Page $page The Page from loop in getPageTree()
     *
     * @return Collection
     */
    protected function convertLabels(Label $labelTree, array $labels, $page) {
        if (is_array($labels)) {
            foreach($labels as $label) {
                if (is_array($label)) {
                    $key = array_keys($label)[0];
                    $childLabel = new Label($key, $page);
                    $this->convertLabels($childLabel, $label[$key], $page);
                    $labelTree->children[$childLabel->name] = $childLabel;
                } else {
                    $childLabel = new Label($label, $page);
                    $labelTree->children[$label] = $childLabel;
                }
            }
        } else {
            $labelTree->children[$label] = new Label($labels, $page);
        }
    }

    /**
     * Merge label from page into existing single labelTree.
     *
     * @param Label &$labelTree Result of merged page labels.
     * @param Label[] $labels Array of Labels from page
     */
    protected function mergeTree(Label &$labelTree, array $labels)
    {
        foreach($labels as $label) {
            if (array_key_exists($label->name, $labelTree->children)) {
                if ($label->children) {
                    $this->mergeTree($labelTree->children[$label->name], $label->children);
                }
                // Add current page to child of $labelTree
                $labelTree->children[$label->name]->pagePaths[] = $label->pagePaths[0];
            } else {
                // $label->name does not occur in $labelTree->children
                $labelTree->children[$label->name] = $label;
            }
        }
    }
}
