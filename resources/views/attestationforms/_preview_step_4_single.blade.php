<div class="card card-primary card-outline">
    <div class="card-header">
        <div class="card-title">Оценка на поставените цели</div>
    </div>
    <div class="card-body score-section disabled">
        <table class="table table-bordered">
            <tbody>
                @foreach( $goal_score_types as $score_type )
                <tr>
                    <td>
                        <label class="form-label">{{ $score_type->text_score }}</label>
                    </td>
                    <td>{{ $score_type->points }} точки</td>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"@checked($attestation_form_scores && $attestation_form_scores->scores && $score_type->id == $attestation_form_scores->scores->goals_score) disabled >
                        </div>
                    </td>
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
                <p class="mb-3">(максимални {{ sizeof( $score_types ) }} точки)</p>
                @foreach( $score_types as $score_type )
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" @checked($attestation_form_scores && $attestation_form_scores->scores && in_array($score_type->id, $attestation_form_scores->scores->competence_score)) disabled >
                    <label class="form-check-label">{{ $score_type->text_score }}</label>
                </div>
                @endforeach
            @endforeach
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
                            $attestation_form_scores->scores &&
                            $attestation_form_scores->scores->total_score >= $score_type->from_points && 
                            $attestation_form_scores->scores->total_score <= $score_type->to_points 
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
            <textarea class="form-control" rows="6" disabled>{{ $attestation_form_scores && $attestation_form_scores->add_info ? $attestation_form_scores->add_info->arguments:'' }}</textarea>
        </div>
        <div class="mb-3 col-md-12">
            <label for="sources" class="form-label">Данни и източници на информация за оценката*</label>
            <textarea class="form-control"rows="6" disabled>{{ $attestation_form_scores && $attestation_form_scores->add_info ? $attestation_form_scores->add_info->sources:'' }}</textarea>
        </div>
        <div class="mb-3 col-md-12">
            <label for="needs" class="form-label">Идентифицирани потребности от обучение*</label>
            <textarea class="form-control" rows="6" disabled>{{ $attestation_form_scores && $attestation_form_scores->add_info ? $attestation_form_scores->add_info->needs:'' }}</textarea>
        </div>
    </div>
</div>