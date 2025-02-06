<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Allergy Test Result</title>
    @include('pdf.allergy-test-swedish-result.styles', ['domain' => $domain])
</head>
<body>
    @include('pdf.allergy-test-swedish-result.footer', ['domain' => $domain])

    <div class="allergy-result">
        <div class="content">
            @include('pdf.allergy-test-swedish-result.content-header', ['domain' => $domain])

            <div class="result-text">
                @if (data_get($result, 'result_type') === 4)
                    @include('pdf.allergy-test-swedish-result.type4', ['domain' => $domain, 'result' => $result])
                @elseif(data_get($result, 'result_type') === 3)
                    @include('pdf.allergy-test-swedish-result.type3', ['domain' => $domain, 'result' => $result])
                @elseif(data_get($result, 'result_type') === 2)
                    @include('pdf.allergy-test-swedish-result.type2', ['domain' => $domain, 'result' => $result])
                @endif
            </div>

        </div>

        <div class="questions-result">
            @include('pdf.allergy-test-swedish-result.result-header', ['domain' => $domain])
            @foreach(data_get($result, 'answers') ?: [] as $answer)
                @if(!empty(collect(data_get($answer, 'choices'))->pluck('value')->filter()->toArray()))
                    <div class="question-wrapper">
                        <ul>
                            <li class="question-header">
                                {{ data_get($answer, 'title') }}
                            </li>
                            @foreach(data_get($answer, 'choices') ?: [] as $choice)
                                <li class="question-item">
                                    @if(!empty(data_get($choice, 'text')))
                                        <span class="question">
                                      {{ data_get($choice, 'text') }}
                                    </span>
                                    @endif
                                    <span class="question"
                                          style="{{ !empty(data_get($choice, 'text')) ? 'float: right; text-align: right;' : '' }}">
                                  {{ data_get($choice, 'value') }}
                                </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</body>
</html>
