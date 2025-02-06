<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Allergy Test Result</title>
    <style>
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            margin-bottom: -20px;
        }
        body {
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
            font-weight: normal;
            -webkit-font-smoothing: antialiased;
            line-height: 1.4;
            color: #3C3C3C;
            margin: 50px;
        }

        h2, h3 {
            color: #FF6651;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 600;
        }

        h2 {
            font-size: 30px;
            font-weight: 100;
            line-height: 1.25em;
            margin: 0;
        }

        h3 {
            line-height: 1.25em;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 5px;
        }

        p {
            margin-top: 0;
            font-size: 12px;
        }

        small {
            font-size: 8px;
        }

        .content {
            margin: 0;
        }

        .header {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .result-text {
            margin-bottom: 20px;
        }

        .question-wrapper {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }

        .question-wrapper ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .question-wrapper li {
            padding: 10px 10px;
            border-left: 1px solid #E2E2E2;
            border-right: 1px solid #E2E2E2;
            border-bottom: 1px solid #E2E2E2;
            font-size: 0;
        }

        .question-wrapper .question-header {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 100;
            font-size: 14px;
            padding: 10px 0;
            border-left: 0;
            border-right: 0;
            color: #FF6651;
        }

        .question-wrapper .question-item .question {
            font-size: 12px;
        }
    </style>
</head>
<body>
@if($domain === \App\Models\User::ALK_NO_STORE)
    <footer class="footer" style="width: 100%; text-align: center;">
        <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/pollenkontroll_logo.png')))}}"
             alt="Logo"
             style="width: 125px;"
        />
    </footer>
@endif
<div class="allergy-result">
    <div class="content">
        <h2 class="header">
            Dit resultat
        </h2>

        <div class="result-text">
            @if (data_get($result, 'result_type') === 4)
                <p>Til tross for bruk av medisiner, plages du av pollenallergien. Det finnes forskjellige måter å
                    behandle pollenallergi på. Det er medisiner som lindrer symptomer slik som nesespray og
                    antihistamintabletter, og det finnes allergivaksinasjon som behandler årsaken til allergien. De
                    aller fleste kan få hjelp med riktig medisinering.</p>
                <p>Hvilken behandling som passer best for deg, avhenger av flere forskjellige faktorer, for eksempel
                    type symptomer, din generelle tilstand, hvilken type behandling du ønsker og bivirkningene
                    medisinene kan gi.</p>
                <p>Ved ditt neste legebesøk, bør du diskutere hvilke behandlingsalternativer som er best for deg.</p>
                <p>Skriv gjerne ut denne allergitesten og vis den til legen din.</p>
                <p>
                    <strong>
                        Denne testen erstatter ikke et legebesøk. Hvis symptomene dine vedvarer, bør du avtale en time
                        hos legen din.
                    </strong>
                </p>
                <h3>Din VAS-poengsum:{{data_get($result, 'vas_score')}}</h3>
                <p>VAS scoren forteller deg hvor mye ubehag du opplever av allergien din selv om du bruker medisiner</p>
                <p><strong>0-2 poeng:</strong> Dine plager er ikke så store. Du kan fortsette med din nåværende
                    behandling.</p>
                <p><strong>2-5 poeng:</strong> Du opplever mild til moderat ubehag. Ta kontakt med din lege og sammen
                    kan dere finne en måte å optimalisere din nåværende behandling.</p>
                <p><strong>5+ poeng:</strong>Til tross for bruk av medisiner ser det ut til at du har store problemer,
                    noe som sikkert påvirker livskvaliteten din i pollensesongen. Kontakt din fastlege for videre
                    utredning. Allergivaksinasjon kan være neste skritt i din behandling.</p>
                <p style="font-size: 8px;">
                    Referanse:Bousguet, P. J et al. Allergic rhinitis. Nature reviews, 2020; 6:95
                </p>
            @elseif(data_get($result, 'result_type') === 3)
                <p>Du blir behandlet for din pollenallergi og føler deg symptomfri. Hvis allergien din forverres, og du
                    ikke lenger blir hjulpet med dagens behandling, anbefales det å søke hjelp hos din fastlege. De
                    aller fleste kan få hjelp med riktig medisinering. Hvilken behandling som er best for deg, avhenger
                    av flere forskjellige faktorer, for eksempel type symptomer, din generelle tilstand, hvilken type
                    behandling du ønsker og bivirkningene medisinene kan gi.</p>
                <p>
                    <strong>
                        Denne testen erstatter ikke et legebesøk Hvis symptomene dine forverrer seg, bør du kontakte
                        legen din.
                    </strong>
                </p>
            @elseif(data_get($result, 'result_type') === 2)
                <p>Du har for øyeblikket ingen behandling for pollenallergien din. Hvilken behandling som er best for
                    deg, avhenger av flere forskjellige faktorer, for eksempel typen symptomer, din generelle tilstand,
                    hvilken type behandling du ønsker og bivirkningene medisinene kan gi. Spør apotekpersonalet eller ta
                    kontakt med fastlegen din for å diskutere hvilken behandling som er best for deg.</p>
                <p>
                    <strong>Denne testen erstatter ikke et legebesøk. Hvis symptomene dine vedvarer, bør du avtale en
                        time hos legen din.</strong>
                </p>
            @endif
        </div>

    </div>

    <div class="questions-result">
        <h3>Her er resultatet ditt</h3>
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
