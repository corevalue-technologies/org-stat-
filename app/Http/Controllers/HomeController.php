<?php

namespace App\Http\Controllers;

use App\Author;
use App\Repository;
use App\WeeklyCommit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function home(Request $request){
        $requestData = $request->all();
        $reportTypes = array (
            'commits_by_week' => 'By Week',
            'commits_by_project' => 'By Project',
            'commits_by_author' => 'By Author'
        );

        $outputTypes = array (
            'absolute' => 'Absolute Values',
            'percentage' => 'Percentage'
        );

        if(!isset($requestData['report_type'])) $requestData['report_type'] = 'commits_by_week';
        if(!isset($requestData['output_type'])) $requestData['output_type'] = 'absolute';
        if(!isset($requestData['date_from'])) $requestData['date_from'] = '2019-12-01';
        if(!isset($requestData['date_to'])) $requestData['date_to'] = date('Y-m-d');



        $authorCommits = array();
        $repoCommits = array();
        $overAllStats = array();
        $weeks = array();
        $authors = array();
        $repos = array();
        $commitsMap = array();
        $commitsAuthorRepo = array();
        if (isset($requestData['button'])) {
            // Get Author Info
            $dataAuthors = Author::all()->toArray();
            foreach($dataAuthors as $dataAuthor) {
                $authors[$dataAuthor['id']] = $dataAuthor;
            }
            // Get Repo Info
            $dataRepositories = Repository::all()->toArray();

            foreach($dataRepositories as $dataRepository) {
                $repos[$dataRepository['id']] = $dataRepository;
            }

            // Get Weekly Commits

            $weeklyCommits = Repository::select('author_id', 'repository_id', 'week', 'additions', 'deletions', 'commits')
                ->leftJoin('weekly_commits','weekly_commits.repository_id', '=', 'repositories.id')
                ->leftJoin('authors','weekly_commits.author_id', '=', 'authors.id')
                ->where('week', '>=', strtotime($requestData['date_from']))
                ->where('week', '<=', strtotime($requestData['date_to']))
                ->orderBy('week')->get()->toArray();
            foreach($weeklyCommits  as $weeklyCommit) {
                $commitsMap [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] [$weeklyCommit['week']] ['additions'] = $weeklyCommit ['additions'];
                $commitsMap [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] [$weeklyCommit['week']] ['deletions'] = $weeklyCommit ['deletions'];
                $commitsMap [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] [$weeklyCommit['week']] ['commits'] = $weeklyCommit ['commits'];

                $weeks[$weeklyCommit['week']] = $weeklyCommit['week'];

                if(!isset($commitsAuthorRepo))
                    $commitsAuthorRepo = array();
                if(!isset($commitsAuthorRepo[$weeklyCommit['author_id']]))
                    $commitsAuthorRepo[$weeklyCommit['author_id']] = array();
                if(!isset($commitsAuthorRepo [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']]))
                    $commitsAuthorRepo [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] = array();
                if(!isset($commitsAuthorRepo [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] ['total_commits']))
                    $commitsAuthorRepo [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] ['total_commits'] = 0;

                $commitsAuthorRepo [$weeklyCommit['author_id']] [$weeklyCommit['repository_id']] ['total_commits'] += $weeklyCommit ['commits'];

                if(!isset($repos[$weeklyCommit['repository_id']])) {
                    $repos[$weeklyCommit['repository_id']] = array();
                }
                if(!isset($repos[$weeklyCommit['repository_id']]['total_commits'])) {
                    $repos[$weeklyCommit['repository_id']]['total_commits'] = 0;
                }
                $repos[$weeklyCommit['repository_id']]['total_commits'] += $weeklyCommit ['commits'];

                if(!isset($authors[$weeklyCommit['author_id']])) {
                    $authors[$weeklyCommit['author_id']] = array();
                }
                if(!isset($authors[$weeklyCommit['author_id']]['total_commits'])) {
                    $authors[$weeklyCommit['author_id']]['total_commits'] = 0;
                }
                $authors[$weeklyCommit['author_id']]['total_commits'] += $weeklyCommit ['commits'];
            }

            asort($weeks);

            foreach ($authors AS $author_id => &$author) {
                if(!isset($author['total_commits'])) {
                    $author['total_commits'] = 0;
                }
                if ($author['total_commits'] > 0) {
                    $authorCommits[$author_id] = $author['total_commits'];
                } else {
                    // Remove authors without any commits
                    unset($authors[$author_id]);
                }
            }

            foreach ($repos AS $repository_id => &$repo) {
                if(!isset($repo['total_commits'])) {
                    $repo['total_commits'] = 0;
                }
                if ($repo['total_commits'] > 0) {
                    $repoCommits[$repository_id] = $repo['total_commits'];
                } else {
                    // Remove repos without any commits
                    unset($repos[$repository_id]);
                }
            }

            arsort($authorCommits);
            arsort($repoCommits);

        } else {
            $overAllStats = Repository::select('fullname', DB::raw('count(1) AS total_repos'))->groupBy('fullname')->orderBy(DB::raw('count(1)'), 'DESC')->get()->toArray();
            foreach ($overAllStats as &$overAllStat) {
                $overAllStat['repositories'] = Repository::select(DB::raw('group_concat(name) as repo_names'))->where('fullname','=',$overAllStat['fullname'])->first()->toArray();

                $repositories = Repository::where('fullname','=',$overAllStat['fullname'])->get()->toArray();
                foreach ($repositories  as $repository) {
                    $overAllStat['author'] = Author::select('authors.html_url', 'authors.avatar_url')->leftJoin('weekly_commits', 'weekly_commits.author_id', '=', 'authors.id')
                        ->leftJoin('repositories', 'repositories.id', '=', 'weekly_commits.repository_id')
                        ->where('repositories.name', '=', $repository['name'])->first();
                    if($overAllStat['author'] != null) {
                        $overAllStat['author'] = $overAllStat['author']->toArray();
                    }
                    else {
                        $overAllStat['author']['html_url'] = '#';
                        $overAllStat['author']['avatar_url'] = 'https://via.placeholder.com/400x400?text=NO+IMAGE';
                    }
                }
            }
        }
       // print_r($authorCommits);exit();

        return view('home',
            [
                'outputTypes'       => $outputTypes,
                'reportTypes'       => $reportTypes,
                'request'           => $requestData,
                'overAllStats'      => $overAllStats,
                'authorCommits'     => $authorCommits,
                'repoCommits'       => $repoCommits,
                'commitsAuthorRepo' => $commitsAuthorRepo,
                'commitsMap'        => $commitsMap,
                'weeks'             => $weeks,
                'authors'           => $authors,
                'repos'             => $repos
            ]
        );
    }

}
