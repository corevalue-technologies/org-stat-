<?php

use Illuminate\Database\Seeder;
use Github\Client;
use Github\HttpClient;
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

        $client = new Client(
            new HttpClient\Builder(array('cache_dir' => '/tmp/github-api-cache'))
        );

        $client->authenticate(env('GITHUB_PERSONAL_ACCESS_TOKEN_USER'), env('GITHUB_PERSONAL_ACCESS_TOKEN_PASSWORD'), Client::AUTH_HTTP_PASSWORD);

        echo "......................................................".PHP_EOL;
        echo "..................Fetching repositories...............".PHP_EOL;
        echo "......................................................".PHP_EOL;

        // Relevant documentation:
        // https://developer.github.com/v3/repos/#list-organization-repositories
        // https://github.com/KnpLabs/php-github-api/blob/master/lib/Github/Api/Repo.php
        // https://github.com/KnpLabs/php-github-api/blob/master/doc/result_pager.md

        $organizationApi = $client->api('organization');
        $paginator  = new \Github\ResultPager($client);
        $parameters = array(env('ORGANISATION'));
        $repos      = $paginator->fetchAll($organizationApi, 'repositories', $parameters);

// Insert repos

        foreach ($repos AS $repo) {
            echo $repo['name'] ."\n";

            $commits = $client->api('repo')->commits()->all(env('ORGANISATION'), $repo['name'], array('sha' => 'master'));

            Repository::create(array(
                'id' => $repo['id'],
                'name' => $repo['name'],
                'fullname' => $commits[0]['commit']['author']['name'],
                'html_url' => $repo['html_url']
            ));

        }

        echo "......................................................".PHP_EOL;
        echo "...............Fetching weekly commits................".PHP_EOL;
        echo "......................................................".PHP_EOL;

        foreach($repos AS $repo) {

            $repo_authors = $client->api('repo')->statistics(env('ORGANISATION'), $repo['name']);


            $organizationApi = $client->api('repositories');
            $paginator       = new \Github\ResultPager($client);
            $parameters      = array(env('ORGANISATION'), $repo['name']);
            $repo_authors    = $paginator->fetchAll($organizationApi, 'statistics', $parameters);


            // Test query - SELECT datetime(week,'unixepoch'), * FROM weekly_commits AS wc INNER JOIN authors AS a ON wc.author_id = a.id INNER JOIN repos AS r ON wc.repo_id=r.id WHERE login='cmorillo' ORDER BY week ASC;

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
                            'repo_id' => $repo['id'],
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
