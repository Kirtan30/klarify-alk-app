<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <style>
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

        .question-wrapper .question-item { }

        .question-wrapper .question-item .question {
            display: inline-block;
            width: 70%;
            font-size: 12px;
        }

        .question-wrapper .question-item .answer {
            display: inline-block;
            width: 30%;
            text-align: right;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="allergy-result">
    @if($domain === \App\Models\User::ALK_DE_STORE)
    <div class="content">
        <h2 class="header">
            Deine Antworten zeigen...
        </h2>

        <div class="result-text">
            @if (data_get($result, 'result_type') === 1)
                <p>
                    ..., dass deine Symptome derzeit gut unter Kontrolle sind. Das ist erfreulich!
                </p>
                <p>
                    <strong>
                        Überprüfe regelmäßig deine Allergiesymptome. Wenn du den Verdacht hast, an Heuschnupfen zu leiden, ist die Pollensaison ein guter Zeitpunkt, um den Test zu wiederholen.
                    </strong>
                </p>
                <p>Wenn du weitere Ratschläge zum Umgang mit deinen Symptomen wünschst, empfehlen wir dir, einen Termin bei deiner Ärztin oder deinem Arzt oder einer allergologischen Fachpraxis zu vereinbaren.</p>
            @else
                <p>
                    <strong>
                        ..., dass deine Symptome derzeit nicht gut kontrolliert sind.
                    </strong><br>
                Wir empfehlen dir, einen Termin mit deiner Ärztin oder deinem Arzt oder einer allergologischen Fachpraxis zu vereinbaren, um deine Symptome und mögliche Behandlungsoptionen zu besprechen. </p>
            @endif
        </div>

    </div>
    @else
    <div class="content">
        <h2 class="header">
            Je antwoorden geven aan …
        </h2>

        <div class="result-text">
            @if (data_get($result, 'result_type') === 1)
                <p>…. dat je klachten momenteel goed onder controle zijn. Dat is goed nieuws!</p>
                <p>
                    <strong>
                        Herhaal de test regelmatig. Controleer het succes wanneer je twijfelt of je je allergieklachten nog steeds goed onder controle hebt. In geval van hooikoorts is de piek van het pollenseizoen een goed moment om de test weer te doen. Of wanneer je last hebt van bijvoorbeeld huisstofmijtallergie zou je weer een check kunnen doen in het najaar/winter.
                    </strong>
                </p>
                <p>Als je meer advies wilt of behandelmogelijkheden wilt bespreken, adviseren wij je een afspraak met je behandelend arts te maken. </p>
            @else
                <p>
                    <strong>
                        …dat je klachten momenteel niet goed onder controle zijn.
                    </strong>
                </p>
                <p>Het kan handig zijn om een afspraak te maken met je huisarts of een specialist (bijvoorbeeld een KNO-arts of allergoloog) om je symptomen en mogelijkheden voor behandeling te bespreken. Klik hier om je antwoorden te downloaden.</p>
            @endif
        </div>

    </div>
    @endif

    <div class="questions-result">
        @foreach(data_get($result, 'answers') ?: [] as $answer)
            <div class="question-wrapper">
                <ul>
                    <li class="question-header">
                        {{ data_get($answer, 'name') }}
                    </li>
                    <li class="question-item">
                        <span class="question">
                          {{ data_get($answer, 'value') }}
                        </span>
                    </li>
                </ul>
            </div>
        @endforeach
    </div>
</div>
</body>
</html>
