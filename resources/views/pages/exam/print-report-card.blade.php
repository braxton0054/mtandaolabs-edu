@extends('layouts.print')

@section('title', 'Report Card - '.$student->name)

@section('content')
    <h2>Learner Report Card - {{$label}}</h2>

    <table>
        <tr>
            <th>Name</th>
            <td>{{$student->name}}</td>
            <th>Class</th>
            <td>{{$class->name}}</td>
        </tr>
        <tr>
            <th>Admission No</th>
            <td>{{$admission_number}}</td>
            <th>Level</th>
            <td>{{$class->level?->label() ?? ''}}</td>
        </tr>
    </table>

    <br/>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Subject</th>
                <th>Marks</th>
                <th>%</th>
                <th>Competency</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($card['rows'] as $index => $row)
                <tr>
                    <td>{{$index + 1}}</td>
                    <td>{{$row['subject']}}</td>
                    <td>{{$row['obtained']}} / {{$row['attainable']}}</td>
                    <td>{{$row['percent']}}</td>
                    <td>{{$row['competency'] ?? '-'}}</td>
                    <td>{{$row['grade']}}</td>
                    <td>{{$row['remark']}}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="2">Total</th>
                <th>{{$card['total']}} / {{$card['attainable']}}</th>
                <th>{{$card['percent']}}</th>
                <th>{{$card['competency'] ?? '-'}}</th>
                <th>{{$card['grade']}}</th>
                <th></th>
            </tr>
        </tbody>
    </table>

    <br/>

    <table>
        <tr>
            <th>EE</th>
            <td>Exceeding Expectation (80-100)</td>
            <th>ME</th>
            <td>Meeting Expectation (65-79)</td>
        </tr>
        <tr>
            <th>AP</th>
            <td>Approaching Expectation (50-64)</td>
            <th>BE</th>
            <td>Below Expectation (0-49)</td>
        </tr>
    </table>

    <br/>
    <br/>

    <table>
        <tr>
            <td style="width: 50%;">Class Teacher: ____________________</td>
            <td>Head Teacher: ____________________</td>
        </tr>
        <tr>
            <td>Signature: ____________________</td>
            <td>Signature: ____________________</td>
        </tr>
    </table>
@endsection
