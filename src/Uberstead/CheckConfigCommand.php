<?php
namespace Uberstead;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CheckConfigCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('test:check_config')
            ->setDescription('Check uberstead.yaml file structure')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (posix_getuid() != 0) {
            $output->writeln('This command needs root access in order to edit your host file. Please run as root (sudo).');
            die();
        }

        exec('echo ~', $out);
        if (!file_exists($out[0].'/.ssh/id_rsa.pub')) {
            $output->writeln('It seems like you don\'t have any SSH keys. Run <question>ssh-keygen -t rsa -C "your_email@example.com"</question> to generate keys.');
            die();
        }

        $filename = 'uberstead.yaml';
        if (!file_exists($filename)) {

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion('<question>uberstead.yaml does not exist! Would you like to generate it? [y]</question>', true);

            $output->writeln('');

            if ($helper->ask($input, $output, $question)) {
                $question = new Question('Which IP would you like to assign to the server? [192.168.10.10]: ', '192.168.10.10');
                $ip = $helper->ask($input, $output, $question);
                $question = new Question('Amount of memory [2048]: ', '2048');
                $memory = $helper->ask($input, $output, $question);
                $question = new Question('Number of CPU cores [1]: ', '1');
                $cpu = $helper->ask($input, $output, $question);
            }

            $yaml = <<<EOF
ip: "{$ip}"
memory: {$memory}
cpus: {$cpu}

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

defaultfoldertype: nfs

sites:
EOF;
            file_put_contents($filename, $yaml);
            chmod($filename, 0664);
            chown($filename, end(explode('/', $out[0])));
        }
    }
}
