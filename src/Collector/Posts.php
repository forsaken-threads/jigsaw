<?php namespace TightenCo\Jigsaw\Collector;

class Posts implements Collection
{
    protected $posts;

    public function collect($post)
    {
        return $this->posts[] = $this->setDefaults($post);
    }

    public function getPosts($sort = false)
    {
        switch ($sort) {
            case 'month':
                return collect(array_reverse($this->posts))->transform(function ($post) {
                    $post['month'] = date('F Y', strtotime($post['published']));
                    return $post;
                })->groupBy('month');
            default:
                return array_reverse($this->posts);
        }
    }

    protected function setDefaults($post)
    {
        if (empty($post['slug'])) {
            $post['slug'] = str_slug($post['title']);
        }
        return $post;
    }
}