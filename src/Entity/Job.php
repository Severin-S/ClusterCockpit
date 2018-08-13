<?php

namespace App\Entity;

use AppBundle\Entity\Node;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
*  @ORM\Entity(repositoryClass="App\Repository\JobRepository")
*/
class Job
{
    /**
     *  @ORM\Column(type="integer")
     *  @ORM\Id
     *  @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     *  @ORM\Column(type="string")
     */
    private $jobId;

    /**
     *  @ORM\ManyToOne(targetEntity="Project")
     */
    private $project;

    /**
     *  @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     *  @ORM\ManyToOne(targetEntity="Cluster")
     */
    private $cluster;

    /**
     *  @ORM\Column(type="string", nullable=true)
     */
    private $queue;

    /**
     *  @ORM\Column(type="integer")
     */
    private $numNodes;

    /**
     *  @ORM\Column(type="integer")
     */
    public $startTime;

    /**
     *  @ORM\Column(type="integer", nullable=true)
     */
    public $stopTime;

    /**
     *  @ORM\Column(type="integer", nullable=true)
     */
    public $duration;

    /**
     *  @ORM\Column(type="integer", nullable=true, options={"default":0})
     */
    public $severity;

    /**
     * @ORM\ManyToMany(targetEntity="Node", indexBy="id")
     * @ORM\JoinTable(name="jobs_nodes", joinColumns={@ORM\JoinColumn(name="job_id", referencedColumnName="id")}, inverseJoinColumns={@ORM\JoinColumn(name="node_id", referencedColumnName="id")})
     */
    private $nodes;

    /**
     * @ORM\OneToOne(targetEntity="JobCache")
     * @ORM\JoinColumn(name="cache_id", referencedColumnName="id"))
     */
    public $jobCache;

    /**
     *  @ORM\Column(type="text", nullable=true)
     */
    private $jobScript;

    /**
     *  @ORM\Column(type="float", options={"default":0})
     */
    public $memUsedAvg;

    /**
     *  @ORM\Column(type="float", options={"default":0})
     */
    public $memBwAvg;

    /**
     *  @ORM\Column(type="float", options={"default":0})
     */
    public $flopsAnyAvg;

    /**
     *  @ORM\Column(type="float", options={"default":0})
     */
    public $networkIO;

    /**
     *  @ORM\Column(type="float", options={"default":0})
     */
    public $fileIO;

    public $hasProfile;

    public function __construct() {
        $this->nodes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getCluster()
    {
        return $this->cluster;
    }

    public function setCluster($cluster)
    {
        $this->cluster = $cluster;
    }

    public function getNumNodes()
    {
        return $this->numNodes;
    }

    public function setNumNodes($numNodes)
    {
        $this->numNodes = $numNodes;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getStopTime()
    {
        return $this->stopTime;
    }

    public function setStopTime($stopTime)
    {
        $this->stopTime = $stopTime;
    }

    public function getNodeIdArray()
    {
        $arr;

        foreach ( $this->nodes as $node ) {
            $arr[] = $node->getNodeId();
        }

        return $arr;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function addNode($node)
    {
        if ($this->nodes->contains($node)) {
            return $node;
        }

        $this->nodes[] = $node;
    }

    public function removeNode($node)
    {
        $this->nodes->removeElement($node);
    }

    public function getNode($id)
    {
        if (!isset($this->nodes[$id])) {
            throw new \InvalidArgumentException("No node with id $id in Job.");
        }

        return $this->nodes[$id];
    }


    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getJobScript()
    {
        return $this->jobScript;
    }

    public function setJobScript($jobScript)
    {
        $this->jobScript = $jobScript;
    }

    public function isRunning()
    {
        return false;
    }
}

