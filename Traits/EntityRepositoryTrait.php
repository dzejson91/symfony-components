<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Traits;

use Doctrine\ORM\Query;
use LangBundle\Classes\LangService;
use LangBundle\Entity\Lang;
use TranslationEntityBundle\EventListener\TranslatableSubscriber;
use TranslationEntityBundle\Query\TranslationWalker;

/**
 * Trait EntityRepositoryTrait
 * @package JasonMx\Components\Traits
 */
trait EntityRepositoryTrait
{
    /**
     * turn on/off query cache
     * @var bool $cacheActive
     */
    public $cacheActive = true;

    /**
     * @param Query $query
     * @return Query
     */
    public function addCacheToQuery($query, $add = true){
        if($this->cacheActive){
            $query
                ->setCacheable(false)
                ->useQueryCache($add)
                ->useResultCache($add, 0)
            ;
        }

        return $query;
    }

    /**
     * @param Query $query
     * @param string|null $locale
     * @param bool|null $fallback
     * @return Query
     */
    public function addTranslationWalker(Query $query, $locale = null, $fallback = null)
    {
        if(isset($locale)){
            $query->setHint(TranslatableSubscriber::HINT_TRANS_LOCALE, $locale);
        } else {
            $lang = LangService::getActiveEditLang();
            if($lang instanceof Lang){
                $query->setHint(TranslatableSubscriber::HINT_TRANS_LOCALE, $lang->getLocale());
            }
        }

        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            TranslationWalker::class
        );

        if(isset($fallback)){
            $query->setHint(TranslatableSubscriber::HINT_MASTER_FALLBACK, $fallback);
        }

        return $query;
    }
}