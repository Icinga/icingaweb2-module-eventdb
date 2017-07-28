<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Forms\Events;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Data\Filter\FilterChain;
use Icinga\Data\Filter\FilterExpression;
use Icinga\Data\Filter\FilterMatch;
use Icinga\Module\Eventdb\Event;
use Icinga\Web\Form;

class SeverityFilterForm extends Form
{
    protected $includedPriorities;
    protected $excludedPriorities;

    /**
     * @var FilterChain
     */
    protected $filter;

    /**
     * @var array
     */
    protected $filterEditorParams = array('modifyFilter', 'addFilter');

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setAttrib('class', 'inline severity-filter-form');
    }

    protected function findPriorities(Filter $filter, $sign, &$target)
    {
        if ($filter->isEmpty()) {
            return;
        }

        if ($filter->isChain()) {
            /** @var FilterChain $filter */
            foreach ($filter->filters() as $part) {
                /** @var Filter $part */
                if (! $part->isEmpty() && $part->isExpression()) {
                    /** @var FilterMatch $part */
                    if (strtolower($part->getColumn()) === 'priority' && $part->getSign() === $sign) {
                        $expression = $part->getExpression();
                        if (is_array($expression)) {
                            foreach ($expression as $priority) {
                                $target[(int) $priority] = $part;
                            }
                        } else {
                            $target[(int) $expression] = $part;
                        }
                    }
                } else {
                    /** @var FilterChain $part */
                    foreach ($part->filters() as $or) {
                        /** @var FilterExpression $or */
                        if (strtolower($or->getColumn()) === 'priority' && $or->getSign() === $sign) {
                            $expression = $or->getExpression();
                            if (is_array($expression)) {
                                foreach ($expression as $priority) {
                                    $target[(int) $priority] = $or;
                                }
                            } else {
                                $target[(int) $expression] = $or;
                            }
                        }
                    }
                }
            }
        } else {
            /** @var FilterMatch $filter */
            if (strtolower($filter->getColumn()) === 'priority' && $filter->getSign() === $sign) {
                $expression = $filter->getExpression();
                if (is_array($expression)) {
                    foreach ($expression as $priority) {
                        $target[(int) $priority] = $filter;
                    }
                } else {
                    $target[(int) $expression] = $filter;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $includedPriorities = array();
        $excludedPriorities = array();
        $params = $this->getRequest()->getUrl()->getParams()
            ->without($this->filterEditorParams)
            ->without('columns')
            ->without('page');

        $filter = Filter::fromQueryString((string) $params);

        $this->findPriorities($filter, '=', $includedPriorities);
        $this->findPriorities($filter, '!=', $excludedPriorities);

        foreach (Event::$priorities as $id => $priority) {
            $class = $priority;
            if (
                (empty($includedPriorities) or isset($includedPriorities[$id]))
                && ! isset($excludedPriorities[$id])
            ) {
                $class .= ' active';
            }
            $label = ucfirst(substr($priority, 0, 1));
            if ($id === 3) {
                $label .= substr($priority, 1, 1);
            }
            $this->addElement(
                'submit',
                $priority,
                array(
                    'class' => $class,
                    'label' => $label
                )
            );
        }

        $this->includedPriorities = $includedPriorities;
        $this->excludedPriorities = $excludedPriorities;

        $this->filter = $filter;
    }

    public function onSuccess()
    {
        $postData = $this->getRequest()->getPost();
        unset($postData['formUID']);
        unset($postData['CSRFToken']);
        reset($postData);
        $priority = Event::getPriorityId(key($postData));
        $redirect = clone $this->getRequest()->getUrl();

        // convert inclusion to exclusion
        if (! empty($this->includedPriorities)) {
            if (empty($this->excludedPriorities)) {
                $this->excludedPriorities = array();
            }
            // set exclusion with for all not included values
            foreach (array_keys(Event::$priorities) as $id) {
                if (! isset($this->includedPriorities[$id])
                    && ! isset($this->excludedPriorities[$id])
                ) {
                    $this->excludedPriorities[$id] = true;
                }
            }
            // purge from inclusions from filter
            if ($this->filter instanceof FilterChain) {
                foreach ($this->includedPriorities as $filter) {
                    if ($filter instanceof Filter) {
                        /** @var Filter $filter */
                        $this->filter = $this->filter->removeId($filter->getId());
                    }
                }
            }
        }

        if ($this->filter instanceof FilterChain) {
            // purge existing exclusions from a complex filter
            foreach ($this->excludedPriorities as $filter) {
                if ($filter instanceof Filter) {
                    /** @var Filter $filter */
                    $this->filter = $this->filter->removeId($filter->getId());
                }
            }
        } elseif (! empty($this->excludedPriorities)) {
            // empty the filter - because it only was a simple exclusion
            $this->filter = new FilterAnd;
        }

        // toggle exclusion
        if (isset($this->excludedPriorities[$priority])) {
            // in exclusion: just remove
            unset($this->excludedPriorities[$priority]);
        } else {
            // not set: add to exclusion
            $this->excludedPriorities[$priority] = true;
        }

        $priorityFilter = Filter::matchAll();
        foreach (array_keys($this->excludedPriorities) as $id) {
            $priorityFilter->andFilter(Filter::expression('priority', '!=', $id));
        }

        if ($this->filter->isEmpty()) {
            // set the Filter
            $this->filter = $priorityFilter;
        } else {
            // append our filter to the rest of the existing Filter
            $this->filter = $this->filter->andFilter($priorityFilter);
        }

        $redirect->setQueryString($this->filter->toQueryString());

        $requestParams = $this->getRequest()->getUrl()->getParams();
        $redirectParams = $redirect->getParams();
        foreach ($this->filterEditorParams as $filterEditorParam) {
            if ($requestParams->has($filterEditorParam)) {
                $redirectParams->add($filterEditorParam);
            }
        }
        if ($requestParams->has('columns')) {
            $redirectParams->add('columns', $this->getRequest()->getUrl()->getParam('columns'));
        }
        $this->setRedirectUrl($redirect);
        return true;
    }
}
