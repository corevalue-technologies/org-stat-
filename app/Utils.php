<?php
namespace App;

class Utils
{
    public static function displayCommits ($output_type, $author_repo_commits, $author_total_commits) {

        if ($author_repo_commits > 0) {
            if ($output_type == 'percentage') {
                return number_format($author_repo_commits / $author_total_commits * 100, 0) . " %";
            } else {
                return $author_repo_commits;
            }
        } else {
            return $output_type == 'percentage' ? '0%' : '0';
        }

    }

}


