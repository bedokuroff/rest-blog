<?php
namespace RestBlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\RestBlogBundle\Repository\DeletedAwareEntityRepository")
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE",region="region_post")
 */
class Post implements \JsonSerializable, DeletedAwareInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $title;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;
    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $author;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $added;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="\RestBlogBundle\Entity\Tag", inversedBy="posts", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="tags_posts")
     */
    private $tags;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $deleted = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->added = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Post
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set author
     *
     * @param string $author
     * @return Post
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return string 
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set added
     *
     * @param \DateTime $added
     * @return Post
     */
    public function setAdded($added)
    {
        $this->added = $added;

        return $this;
    }

    /**
     * Get added
     *
     * @return \DateTime 
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Post
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('deleted', false));

        return $this->tags->matching($criteria);
    }

    /**
     * @param ArrayCollection $tags
     * @return Post
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @param Tag $tag
     * @return Post
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return Post
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Remove tag
     *
     * @param \RestBlogBundle\Entity\Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $tags = [];

        /** @var Tag $tag */
        foreach ($this->getTags() as $tag) {
            $tags[] = $tag->jsonSerialize();
        }

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'content' => $this->getContent(),
            'author' => $this->getAuthor(),
            'added' => $this->getAdded()->format('r'),
            'updated' => $this->getUpdated()->format('r'),
            'tags' => $tags
        ];
    }
}
