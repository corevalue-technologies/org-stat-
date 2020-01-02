<?php

use Illuminate\Database\Seeder;
use App\Repository;
use App\Author;
use App\WeeklyCommit;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        WeeklyCommit::query()->truncate();
        Author::query()->truncate();
        Repository::query()->truncate();
        echo "......................................................".PHP_EOL;
        echo "................Fetching repositories.................".PHP_EOL;
        echo "......................................................".PHP_EOL;

        // Relevant documentation:
        // https://developer.github.com/v3/repos/#list-organization-repositories
        // https://github.com/KnpLabs/php-github-api/blob/master/lib/Github/Api/Repo.php
        // https://github.com/KnpLabs/php-github-api/blob/master/doc/result_pager.md


        $paginator  = new \Github\ResultPager(GitHub::connection());
        $parameters = array(env('GITHUB_ORGANISATION'));
        $repos      = $paginator->fetchAll(GitHub::organizations(), 'repositories', $parameters);
        // Insert repos
        foreach ($repos AS $repo) {
            echo $repo['name'] .PHP_EOL;
            $commits = GitHub::repo()->commits()->all(env('GITHUB_ORGANISATION'), $repo['name'], array('sha' => 'master'));

            Repository::create(array(
                'id' => $repo['id'],
                'name' => $repo['name'],
                'fullname' => $commits[0]['commit']['author']['name'],
                'html_url' => $repo['html_url']
            ));

            echo "......................................................".PHP_EOL;
            echo "...............Fetching weekly commits................".PHP_EOL;
            echo "......................................................".PHP_EOL;

            $paginator      = new \Github\ResultPager(GitHub::connection());
            $parameters     = array(env('GITHUB_ORGANISATION'), $repo['name']);
            $repo_authors   = $paginator->fetchAll(GitHub::repo(), 'statistics', $parameters);

            echo "******* ".$repo['name'] ."[".count($repo_authors)." authors] *******\n";

            foreach ($repo_authors AS $author_data) {

                echo $author_data['author']['login'] . " [" . $author_data['total'] . "]\n";

                // Insert author

                Author::create(array(
                    'id' => $author_data['author']['id'],
                    'username' => $author_data['author']['login'],
                    'avatar_url' => $author_data['author']['avatar_url'],
                    'html_url' => $author_data['author']['html_url']
                ));


                foreach ($author_data['weeks'] AS $weekly_commits)  {

                    // Insert weekly commit information

                    if ($weekly_commits['c'] > 0) {

                        WeeklyCommit::create(array(
                            'repository_id' => $repo['id'],
                            'author_id' => $author_data['author']['id'],
                            'week' => $weekly_commits['w'],
                            'additions' => $weekly_commits['a'],
                            'deletions' => $weekly_commits['d'],
                            'commits' => $weekly_commits['c']
                        ));

                    }
                }
            }
        }
    }
}
