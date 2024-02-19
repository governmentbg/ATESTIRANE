<div class="card card-primary card-outline">
    <div class="card-header">
        <div class="card-title">Оценка на поставените цели</div>
    </div>
    <div class="card-body score-section disabled">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    @foreach($attestation_form_scores_multiple as $attestation_form_score)
                        <th width="2%">Член №{{$loop->iteration}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach( $goal_score_types as $score_type )
                <tr>
                    <td>
                        <label class="form-label">{{ $score_type->text_score }}</label>
                    </td>
                    <td>{{ $score_type->points }} точки</td>
                    @foreach($attestation_form_scores_multiple as $attestation_form_score)
                    <td>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"@checked($attestation_form_score && $attestation_form_score->scores && $score_type->id == $attestation_form_score->scores->goals_score) disabled >
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card card-primary card-outline mt-3">
    <div class="card-header">
        <div class="card-title">Показани компетентности</div>
    </div>
    <div class="card-body score-section disabled">
        <div class="col-md-12">
            @foreach( $competence_score_types as $group_name => $score_types )
                <h5>{{ $group_name }}</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @foreach($attestation_form_scores_multiple as $attestation_form_score)
                                <th width="2%">Член №{{$loop->iteration}}</th>
                            @endforeach
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach( $score_types as $score_type )
                        <tr>
                            @foreach($attestation_form_scores_multiple as $attestation_form_score)
                            <td>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" @checked($attestation_form_score && $attestation_form_score->scores && in_array($score_type->id, $attestation_form_score->scores->competence_score)) disabled >
                                </div>
                            </td>
                            @endforeach
                            <td>
                                <label class="form-label">{{ $score_type->text_score }}</label>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
</div>

<div class="card card-primary card-outline mt-3">
    <div class="card-header">
        <div class="card-title">Обща брой точки, поставени от членовете на атестационната комисия</div>
    </div>
    <div class="card-body disabled">
        <div class="row align-items-end">
            <div class="col-md-6">
                <table class="table table-bordered mb-0">
                    <tbody>
                        @foreach($attestation_form_scores_multiple as $attestation_form_score)
                        <tr>
                            <td>Член №{{$loop->iteration}}</td>
                            <td>{{ $attestation_form_score->total_score }} точки</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-6 text-end">
                <h5>Средноаритметична стойност <span style="border: 2px solid; padding: 10px; margin-left: 20px;">{{ $attestation_form_scores->total_score }} точки</span></h5>
            </div>
        </div>

        
    </div>
</div>

<div class="card card-primary card-outline mt-3">
    <div class="card-header">
        <div class="card-title">Обща оценка на изпълнението на длъжността</div>
    </div>
    <div class="card-body disabled">
        <table class="table table-bordered">
            <tbody>
                @foreach( $total_score_types as $score_type )
                    @php
                        if( 
                            $attestation_form_scores && 
                            floor($attestation_form_scores->total_score) >= $score_type->from_points && 
                            floor($attestation_form_scores->total_score) <= $score_type->to_points 
                        ){
                            $is_score = true;
                        } else {
                            $is_score = false;
                        }
                    @endphp
                <tr class="align-middle {{ $is_score ? 'bg-light-green':'' }}">
                    <td>{{ $score_type->text_score }}</td>
                    <td>
                        @if( $score_type->from_points )
                            от {{ $score_type->from_points }} 
                        @endif
                        до {{ $score_type->to_points }} точки
                    </td>
                    <td class="text-center">
                        @if( $is_score )
                            <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card card-primary card-outline mt-3">
    <div class="card-header">
        <div class="card-title">Допълнителна информация</div>
    </div>
    <div class="card-body score-section disabled">
        <div class="mb-3 col-md-12">
            <label for="arguments" class="form-label">Аргументи за поставената оценка*</label>
            <textarea class="form-control" rows="6" disabled>
@foreach($attestation_form_scores_multiple as $attestation_form_score)
Член №{{$loop->iteration}}: "{!! $attestation_form_score && $attestation_form_score->add_info ? $attestation_form_score->add_info->arguments:'' !!}"
@endforeach
            </textarea>
        </div>
        <div class="mb-3 col-md-12">
            <label for="sources" class="form-label">Данни и източници на информация за оценката*</label>
            <textarea class="form-control" rows="6" disabled>
@foreach($attestation_form_scores_multiple as $attestation_form_score)
Член №{{$loop->iteration}}: "{!! $attestation_form_score && $attestation_form_score->add_info ? $attestation_form_score->add_info->sources:'' !!}"
@endforeach
            </textarea>
        </div>
        <div class="mb-3 col-md-12">
            <label for="needs" class="form-label">Идентифицирани потребности от обучение*</label>
            <textarea class="form-control" rows="6" disabled>
@foreach($attestation_form_scores_multiple as $attestation_form_score)
Член №{{$loop->iteration}}: "{!! $attestation_form_score && $attestation_form_score->add_info ? $attestation_form_score->add_info->needs:'' !!}"
@endforeach
            </textarea>
        </div>
    </div>
</div>