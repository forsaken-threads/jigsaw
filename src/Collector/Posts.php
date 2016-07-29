<?php namespace TightenCo\Jigsaw\Collector;

class Posts implements Collection
{
    protected $posts;

    public function collect($post)
    {
        return $this->posts[] = $this->setDefaults($post);
    }

    public function &currentItem()
    {
        return $this->posts[count($this->posts) - 1];
    }

    public function getPosts($sort = false)
    {
        switch ($sort) {
            case 'month':
                return collect($this->posts)->sortBy('published')->reverse()->transform(function ($post) {
                    $post['month'] = date('F Y', strtotime($post['published']));
                    return $post;
                })->groupBy('month');
            case 'tags':
                return $this->paginateByTags();
            default:
                return collect($this->posts)->sortBy('published')->reverse();
        }
    }

    public function paginateByTags()
    {
        $tags = collect($this->posts)->reduce(function ($carry, $post) {
            return array_merge($carry, $post['tags']);
        }, []);
        $paginated = [];
        foreach ($tags as $tag) {
            $paginated[$tag] = collect($this->posts)->filter(function ($post) use ($tag) {
                return in_array($tag, $post['tags']);
            });
        }
        return $paginated;
    }

    protected function setDefaults($post)
    {
        if (empty($post['slug'])) {
            $post['slug'] = str_slug($post['title']);
        }
        return $post;
    }
}