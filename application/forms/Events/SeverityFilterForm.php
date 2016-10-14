<?php
/* Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Eventdb\Forms\Events;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Data\Filter\FilterMatch;
use Icinga\Data\Filter\FilterOr;
use Icinga\Module\Eventdb\Event;
use Icinga\Module\Eventdb\Eventdb;
use Icinga\Web\Form;

class SeverityFilterForm extends Form
{
    protected $activePriorities;

    /**
     * @var Filter
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

    /**
     * {@inheritdoc}
     */
    public function createElements(array $formData)
    {
        $activePriorities = array();
        $filter = Filter::fromQueryString(
            (string) $this->getRequest()->getUrl()->getParams()->without($this->filterEditorParams)->without('columns')
        );
        if (! $filter->isEmpty()) {
            if ($filter->isChain()) {
                /** @var \Icinga\Data\Filter\FilterChain $filter */
                foreach ($filter->filters() as $part) {
                    /** @var \Icinga\Data\Filter\Filter $part */
                    if (! $part->isEmpty() && $part->isExpression()) {
                        /** @var \Icinga\Data\Filter\FilterMatch $part */
                        if (strtolower($part->getColumn()) === 'priority' && $part->getSign() === '=') {
                            $expression = $part->getExpression();
                            if (is_array($expression)) {
                                foreach ($expression as $priority) {
                                    $activePriorities[(int) $priority] = $part;
                                }
                            } else {
                                $activePriorities[(int) $expression] = $part;
                            }
                        }
                    } else {
                        foreach ($part->filters() as $or) {
                            if (strtolower($or->getColumn()) === 'priority' && $or->getSign() === '=') {
                                $expression = $or->getExpression();
                                if (is_array($expression)) {
                                    foreach ($expression as $priority) {
                                        $activePriorities[(int) $priority] = $or;
                                    }
                                } else {
                                    $activePriorities[(int) $expression] = $or;
                                }
                            }
                        }
                    }
                }
            } else {
                /** @var \Icinga\Data\Filter\FilterMatch $filter */
                if (strtolower($filter->getColumn()) === 'priority' && $filter->getSign() === '=') {
                    $expression = $filter->getExpression();
                    if (is_array($expression)) {
                        foreach ($expression as $priority) {
                            $activePriorities[(int) $priority] = $filter;
                        }
                    } else {
                        $activePriorities[(int) $expression] = $filter;
                    }
                }
            }
        }

        foreach (Event::$priorities as $id => $priority) {
            $class = $priority;
            if (isset($activePriorities[$id])) {
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
                    'class'         => $class,
                    'label'         => $label
                )
            );
        }

        $this->activePriorities = $activePriorities;
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
        if ($this->filter->isEmpty()) {
            $this->filter = FilterAnd::where('priority', $priority);
        } elseif ($this->filter->isExpression()) {
            if (isset($this->activePriorities[$priority])) {
                // Fake empty
                $this->filter = new FilterAnd();
            } elseif (! empty($this->activePriorities)) {
                $this->filter = $this->filter->orFilter(Filter::expression('priority', '=', $priority));
            } else {
                $this->filter = $this->filter->andFilter(Filter::expression('priority', '=', $priority));
            }
        } else {
            foreach ($this->activePriorities as $filter) {
                $this->filter = $this->filter->removeId($filter->getId());
            }
            if (isset($this->activePriorities[$priority])) {
                unset($this->activePriorities[$priority]);
            } else {
                $this->activePriorities[$priority] = true;
            }
            $priorities = array();
            foreach (array_keys($this->activePriorities) as $id) {
                $priorities[] = Filter::expression('priority', '=', $id);
            }
            if ($this->filter->isEmpty()) {
                $this->filter = Filter::matchAny($priorities);
            } else {
                $this->filter = $this->filter->andFilter(
                    Filter::matchAny($priorities)
                );
            }
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
