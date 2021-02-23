<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", length=4)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * @Assert\NotBlank(message="Please enter the task name.")
     * @Assert\Length(min = 3, minMessage = "Task name should be at least 3 characters.",
     * max = 128, maxMessage = "Task name must be shorter than 128 characters.",)
     */
    private $task_name;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Please enter a description for the task.")
     */
    private $task_description;

    /**
     * @ORM\ManyToOne(targetEntity=Status::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Please select the status.")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $add_date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completed_date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskName(): ?string
    {
        return $this->task_name;
    }

    public function setTaskName(string $task_name): self
    {
        $this->task_name = $task_name;

        return $this;
    }

    public function getTaskDescription(): ?string
    {
        return $this->task_description;
    }

    public function setTaskDescription(string $task_description): self
    {
        $this->task_description = $task_description;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAddDate(): ?\DateTimeInterface
    {
        return $this->add_date;
    }

    public function setAddDate(\DateTimeInterface $add_date): self
    {
        $this->add_date = $add_date;

        return $this;
    }

    public function getCompletedDate(): ?\DateTimeInterface
    {
        return $this->completed_date;
    }

    public function setCompletedDate(\DateTimeInterface $completed_date): self
    {
        $this->completed_date = $completed_date;

        return $this;
    }

    private $img;
    
    public function getImg()
    {
        $filesystem = new Filesystem();
        if($filesystem->exists('assets/img/tasks/' . $this->id . '.jpg')){
        return '/assets/img/tasks/'.$this->id . '.jpg';
        }else{
            return '/build/missing.png';
        }
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $img
     */
    public function setImg(UploadedFile $img = null)
    {
        // $this->img = $img;
    }

}
