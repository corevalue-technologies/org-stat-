q<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Github Contributors Statistics</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    </head>
    <body>
        <div class="content">
            <div class="flex-center position-ref full-height">
                <div class="title m-b-md">
                    Github Contributor Statistics
                </div>
                <p>A tool to display all Github contributor statistics for {{env('GITHUB_ORGANISATION')}}.</p>

                <form method="get" action="">
                    <div class="row">
                        <div class='col-md-3'>
                            <div class="form-group">
                                <div class='input-group date' >
                                    <input type='text' required class="form-control" id='fromDate' name="date_from" placeholder="From:" value="" />
                                    <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class="form-group">
                                <div class='input-group date'>
                                    <input type='text' required class="form-control"  id='toDate' name="date_to" placeholder="To:" value="" />
                                    <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class="form-group">
                                <div class='input-group' id='reporttypedropdown'>
                                    <select class="form-control" required name="report_type">
                                        @foreach($reportTypes as $key => $reportType)
                                            <option value="{{$key}}" @if($request['report_type'] == $key) selected @endif>{{$reportType}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class="form-group">
                                <div class='input-group' id='reporttypedropdown'>
                                    <select class="form-control" required name="output_type">
                                        @foreach($outputTypes as $key => $outputType)
                                            <option value="{{$key}}" @if($request['output_type'] == $key) selected @endif>{{$outputType}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button type="submit" id="find-now" class="btn btn-default" name="button">Find Now!</button>
                            <button type="reset" class="btn btn-cancel" name="clear" onclick="document.location='/';">Clear</button>
                        </div>
                    </div>
                </form>
                @if(!isset($_GET['button']))
                    <table class="table table-striped table-dark table-hover">
                        <tr>
                            <th>Name</th>
                            <th>Count</th>
                        </tr>
                        @php
                            $totalCount = 0
                        @endphp
                        @foreach($overAllStats as $overAllStat)
                            <tr>
                                <td>{{$overAllStat['fullname']}}</td>
                                <td class="text-center">{{$overAllStat['total_repos']}}
                                    @php
                                        $totalCount += $overAllStat['total_repos'];
                                    @endphp
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td>Total</td>
                            <td class="text-center">{{$totalCount}}</td>
                        </tr>
                    </table>
                @else
                    <div class="row">
                        <div class="page-header">
                            <h2>{{$reportTypes[$request['report_type']]}}</h2>
                        </div>
                    </div>
                    <div class="row">
                        <table class="table table-striped table-dark table-hover">
                        @switch($request['report_type'])
                            @case('commits_by_week')
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Repository</th>
                                        @php $i=1 @endphp
                                        @foreach($weeks as $week)
                                            <th>{{$i++}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($authors as $author_id => $author)
                                        @foreach ($repos as $repo_id => $repo)
                                            @if (isset($commitsMap[$author_id][$repo_id]))
                                                <tr>
                                                    <td>{{$author['username']}}</td>
                                                    <td>{{$repo['name']}}</td>
                                                    @foreach ($weeks as $week_id => $week)
                                                        @if (isset($commitsMap[$author_id][$repo_id][$week_id]))
            {{--                                                    <td>".display_commits($_GET['output_type'], $commitsMap[$author_id][$repo_id][$week_id]['commits'], $authors[$author_id]['total_commits'])."</td>\n";--}}
                                                            <td>display_commits</td>
                                                        @else
                                                            <td>&nbsp;</td>
                                                        @endif
                                                    @endforeach
                                               </tr>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </tbody>

                            @break
                            @case('commits_by_author')
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>User</th>
                                        <th>Commits</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i=1 @endphp
                                    @foreach ($author_commits as $author_id => $num_commits)
                                    <tr>
                                        <td>{{$i++}}</td>
                                        <td>{{$authors[$author_id]['username']}}</td>
                                        <td>{{$num_commits}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            @break

                            @case('commits_by_project')
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Project</th>
                                    </tr>

                                    </tr>
                                    <th>&nbsp;</th>
                                    @foreach ($repo_commits as $repo_id => $num_commits)
                                    <th>{{$repos[$repo_id]['name']}}</th>
                                    @endforeach
                                    <th>TOTALS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($author_commits as $author_id => $num_commits)
                                    <tr>
                                        <td>{{$authors[$author_id]['username']}}</td>
                                        @foreach ($repo_commits as $repo_id => $num_commits)
                                        <td>
                                            @if(!isset($commits_author_repo[$author_id][$repo_id]['total_commits']))
                                                    $commits_author_repo[$author_id][$repo_id]['total_commits'] = 0;
                                            @endif
                                            @if(!isset($authors[$author_id]['total_commits']))
                                                    $authors[$author_id]['total_commits'] = 0;
                                            @endif
                                            display_commits
                                        </td>
                                        @endforeach
                                        <td>{{$authors[$author_id]['total_commits']}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            @break
                        @endswitch
                        </table>
                    </div>
                @endif
            </div>

            <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
            <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
            <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

            <script type="text/javascript">
                $(function () {
                    $('#fromDate').datepicker({
                        dateFormat: "yy-mm-dd",
                        maxDate: new Date(),
                        onSelect: function(dateStr)
                        {
                            $("#toDate").datepicker("option",{ minDate: new Date(dateStr)})
                        }
                    });
                    $('#toDate').datepicker({
                        dateFormat: "yy-mm-dd",
                        maxDate: new Date(),
                        useCurrent: false,
                        onSelect: function(dateStr)
                        {
                            $("#fromDate").datepicker("option",{ maxDate: new Date(dateStr)})
                        }
                    });
                    $('#fromDate').datepicker('setDate', new Date('{{$request['date_from']}}'));
                    $('#toDate').datepicker('setDate', new Date('{{$request['date_to']}}'));
                });
            </script>
        </div>
    </body>
</html>
