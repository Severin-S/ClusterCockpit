<?php
/*
 *  This file is part of ClusterCockpit.
 *
 *  Copyright (c) 2018 Jan Eitzinger
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

use App\Repository\JobRepository;
use App\Service\JobCache;
use App\Service\ColorMap;
use App\Service\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\JobSearch;
use App\Entity\Job;
use App\Entity\Plot;
use App\Entity\User;
use \DateInterval;

/**
 * Class: ExportJob
 *
 * @see Command
 * @author Jan Eitzinger
 * @version 0.1
 */
class ExportJob extends Command
{
    private $_em;
    private $_filesystem;
    private $_root;
    private $_jobcache;

    public function __construct(
        EntityManagerInterface $em,
        JobCache $jobCache,
        $projectDir,
        FileSystem $filesystem
    )
    {
        $this->_em = $em;
        $this->_jobcache = $jobCache;
        $this->_filesystem = $filesystem;
        $this->_root = $projectDir.'/var/export/';

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:job:export')
            ->setDescription('Export a job to disk')
            ->setHelp('.')
            ->addArgument('id', InputArgument::REQUIRED, 'The jobID for the job to export.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $repository = $this->_em->getRepository(\App\Entity\Job::class);
        $configuration = new Configuration($this->_em);
        $userRep = $this->_em->getRepository(\App\Entity\User::class);
        $users = $userRep->findAll();

        /*         foreach ( $users as $user ) { */

        /*             $name = $user->getName(true); */
        /*             $userID = $user->getUserId(true); */
        /*             $email = $user->getEmail(true); */
        /*             $pass = $user->getPassword(); */

        /*             /1* $output->writeln([ 'TRY ', $name, $pass]); *1/ */

        /*                 $user->setName($name); */
        /*                 $user->setUsername($userID); */
        /*                 $user->setEmail($email); */
        /*                 $this->_em->persist($user); */
        /*         } */
        /*                 $this->_em->flush(); */

        /*         exit; */

        $output->writeln([
            'Job File Export',
            '===============',
            '',
        ]);

        $job = $repository->findOneBy(['jobId' => $id]);

        $rows = array();
        $rows[] = array("Job id",$job->getJobId());
        $rows[] = array("User name",$job->getUser()->getName());
        $rows[] = array("User id",$job->getUser()->getUserId());
        $rows[] = array("NumNodes",$job->getNumNodes());
        $rows[] = array("Duration [h]",  $job->getDuration()/3600);
        $rows[] = array("Start time",date('r', $job->getStartTime()));
        $rows[] = array("Stop time", date('r', $job->getStopTime()));

        $table = new Table($output);
        $table
            ->setHeaders(array('Label', 'Value'))
            ->setRows($rows);
        $table->render();

        $options['plot_view_showPolarplot']      = $configuration->getValue('plot_view_showPolarplot');
        $options['plot_view_showRoofline']       = $configuration->getValue('plot_view_showRoofline');
        $options['plot_view_showStatTable']      = $configuration->getValue('plot_view_showStatTable');
        $options['plot_list_samples']            = $configuration->getValue('plot_list_samples');
        $options['plot_general_colorBackground'] = $configuration->getValue('plot_general_colorBackground');
        $options['plot_general_colorscheme']     = $configuration->getValue('plot_general_colorscheme');
        $options['plot_general_lineWidth']       = $configuration->getValue('plot_general_lineWidth');
        $options['data_time_digits']             = $configuration->getValue('data_time_digits');

        $this->_jobcache->checkCache(
            $job,
            'view',
            $options
        );

        if ( $job->hasProfile ) {
            try {
                $this->_filesystem->mkdir($this->_root.$job->getJobId());
            } catch (IOExceptionInterface $exception) {
                echo "An error occurred while creating job export directory at ".$exception->getPath();
            }

            $jobCache = $job->jobCache;
            /* dump($jobCache); */
            /* exit; */

            /* dump meta information */

            $jsonData = array();
            $jsonData['job_id'] = $job->getJobId();
            $jsonData['user_id'] = $job->getUser()->getUserId();
            $jsonData['cluster_id'] = $job->getCluster()->getName();
            $jsonData['num_nodes'] = $job->getNumNodes();
            $jsonData['start_time'] = $job->getStartTime();
            $jsonData['stop_time'] = $job->getStopTime();
            $jsonData['duration'] = $job->getDuration();
            $jsonData['nodes'] = $job->getNodeIdArray();
            $jsonData['tags'] = $job->getTagsArray();

/*             $nodestring = implode(", ",$job->getNodeIdArray()); */
/*             $tagstring = implode(", ",$job->getTagsArray()); */

/*             $meta = <<<EOT */
/* job_id: {$job->getJobId()} */
/* user_id: {$job->getUser()->getUserId()} */
/* cluster_id: {$job->getCluster()->getName()} */
/* num_nodes: {$job->getNumNodes()} */
/* start_time: {$job->getStartTime()} */
/* stop_time: {$job->getStopTime()} */
/* duration: {$job->getDuration()} */
/* tags: [$tagstring] */
/* nodes: [$nodestring] */
/* EOT; */

/*             $this->_filesystem->dumpFile($this->_root.$job->getJobId().'/meta.yml', $meta); */

            $output->writeln(['Export to ',
                $this->_root.$job->getJobId()]);

            $plots = $jobCache->getPlots();

            if (isset($plots['roofline'])) {
                unset($plots['roofline']);
            }

            if (isset($plots['polarplot'])) {
                unset($plots['polarplot']);
            }

            $footprint = array();

            foreach ( $plots as $plot ) {
                $nodes = $plot->getData();

                $nodeCache = array();
                $data = $nodes[0];
                $length = count($data['x']);

                for ($j=0; $j<$length; $j++) {
                    $nodeCache[] = "{$data['x'][$j]}";
                }

                $statData = array();

                foreach ($nodes as $node){
                    if ($length > count($node['y'])) {
                        $length = count($node['y']);
                    }

                    for ($j=0; $j<$length; $j++) {
                        $statData[] = $node['y'][$j];
                        $nodeCache[$j] .= " {$node['y'][$j]}";
                    }
                }

                $avg = array_sum($statData) / count($statData);
                $options = $plot->getOptions();

                if (! isset($jsonData['time_unit'])) {
                    if (preg_match("/runtime \[([mh])\]/", $options['xaxis']['title'], $matches)){
                        $jsonData['time_unit'] = $matches[1];
                    }
                }


                $footprint[$plot->name] = array(
                    'unit' => $options['yaxis']['title'],
                    'avg'  => $avg
                );

                $datastring = implode("\n",$nodeCache);
                $this->_filesystem->dumpFile($this->_root.$job->getJobId()."/{$plot->name}.dat", $datastring);
            }

            $jsonData['footprint'] = $footprint;
            $this->_filesystem->dumpFile($this->_root.$job->getJobId().'/meta.json', json_encode($jsonData));
        }
    }
}

