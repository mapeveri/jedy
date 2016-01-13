<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Content;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route("/blog")
 */
class BlogController extends Controller
{
    /**
     * @Route("/", name="app_blog_index")
     */
    public function indexAction()
    {
        return $this->render(
            'blog/blog_index.html.twig',
            [
                'posts' => $this->getDoctrine()->getRepository('AppBundle:Content')->findBy(
                    ['type' => 'post', 'locale' => $this->get('request_stack')->getMasterRequest()->get('_locale')]
                ),
            ]
        );
    }

    /**
     * @Route("/post/{slug}", name="app_blog_post")
     */
    public function postShowAction(Content $post)
    {
        return $this->render('blog/blog_post.html.twig', ['post' => $post]);
    }
}