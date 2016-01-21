<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Content;
use AppBundle\Util\Locales;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Container;

class AppPublicExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $locales;

    /**
     * @var RoutingExtension
     */
    private $routingExtenxion;

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Locales $locales
     * @param RoutingExtension $routingExtenxion
     */
    public function __construct(Locales $locales, RoutingExtension $routingExtenxion, Container $container)
    {
        $this->locales = $locales;
        $this->routingExtenxion = $routingExtenxion;
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('translation_content', [$this, 'translation_content'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('nav_locale', [$this, 'nav_locale'], ['is_safe' => ['html'] ] ),
        );
    }

    /**
     * @param Content $content
     * @param string $class
     * @return string
     */
    public function translation_content(Content $content, $class = 'translation_content')
    {
        $result = "<ul class='".$class."'>";
        foreach ($this->getTranslations($content) as $item) {
            $result .= "<li><a href='".$this->routingExtenxion->getPath(
                    'app_blog_post',
                    ['_locale' => $item['locale'], 'slug' => $item['slug']]
                )."' title='".$item['title']."'>".$item['language']."</a> </li>";
        }
        $result .= "</ul>";

        return $result;
    }

    public function nav_locale($name, $locale)
    {
        $contentsNav = $this->container->get('session')->has($name.$locale) ? $this->container->get('session')->get($name.$locale) : $this->container->get('doctrine')->getRepository('AppBundle:Nav')->findOneBy(['name' => $name, 'locale' => $locale]);

        if($contentsNav){
            $data = $contentsNav->getContentsNav();
            $result = "<ul class='nav navbar-nav'>";

            foreach ($data as $item ) {
                if($item->getType() == 'category') {
                    $route = $this->routingExtenxion->getPath('app_blog_category', ['slug' => $item->getSlug()]);
                    $result .= "<li><a href='".$route."'>".$item->getName()."</a></li>";
                }
                if($item->getType() == 'page'){
                    $route = $this->routingExtenxion->getPath('app_blog_page', ['slug' => $item->getSlug()]);
                    $result .= "<li><a href='".$route."'>".$item->getName()."</a></li>";
                }
            }
            $result .= "</ul>";

            if(!$this->container->get('session')->has($name.$locale)) {
                $this->container->get('session')->set($name.$locale, $contentsNav);
            }
            return $result;
        }
    }

    private function getTranslations(Content $content)
    {
        $contents = [];

        if ($content->getParentMultilangue()) {
            $contents[] = [
                'locale' => $content->getParentMultilangue()->getLocale(),
                'language' => $this->locales->getLanguage($content->getParentMultilangue()->getLocale()),
                'slug' => $content->getParentMultilangue()->getSlug(),
                'title' => $content->getParentMultilangue()->getTitle(),
            ];

            foreach ($content->getParentMultilangue()->getChildrenMultilangue() as $item) {

                if (($item->getLocale() != $content->getLocale()) && $item->getStatus()) {
                    $contents[] = [
                        'locale' => $item->getLocale(),
                        'language' => $this->locales->getLanguage($item->getLocale()),
                        'slug' => $item->getSlug(),
                        'title' => $item->getTitle(),
                    ];
                }
            }
        }

        if ($content->getChildrenMultilangue()) {

            foreach ($content->getChildrenMultilangue() as $item) {
                if ($item->getStatus()) {
                    $contents[] = [
                        'locale' => $item->getLocale(),
                        'language' => $this->locales->getLanguage($item->getLocale()),
                        'slug' => $item->getSlug(),
                        'title' => $item->getTitle(),
                    ];
                }
            }

        }

        return $contents;
    }

    public function getName()
    {
        return 'app_public.twig.extension';
    }
}