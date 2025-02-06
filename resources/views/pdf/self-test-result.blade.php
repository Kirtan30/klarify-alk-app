<html lang="en"><head><meta charset="UTF-8"><title>Uitslag-zelftest</title><style>
        html {
            font-size: 12pt;
        }

        body {
            font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
            font-weight: normal;
            -webkit-font-smoothing: antialiased;
            font-size: 1rem;
            line-height: 1.4;
            color: #3C3C3C;
            margin: 5mm;
        }

        h2, h3 {
            color: #FF6651;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 600;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 100;
            line-height: 1.25em;
            margin: 0 0 10mm;
        }

        h3 {
            line-height: 1.25em;
            font-size: 1rem;
            font-weight: 400;
            margin: 0 0 2.5mm;
        }

        p {
            font-size: 1rem;
            margin: 0;
        }

        hr {
            border: 0;
            background-color: #C5C5C5;
            height: 0.25mm;
            width: 100%;
            display: inline-block;
        }

        .survey__step {
            display: block;
            width: 100%;
            border-top: 0.25mm solid #C5C5C5;
            padding-top: 10mm;
            margin-top: 10mm;
            page-break-inside: avoid;
        }

        .header {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .result-text {
            margin-bottom: 40px;
        }

        .test-score {
            text-align: center;
            font-size: 32px;
            margin: 20px auto;
        }

        table {
            width: 100%;
        }

        table th,
        table td {
            padding: 0;
        }

        table th {
            font-size: 0.75rem;
            font-weight: 400;
        }

        .radiobuttons {
            display: inline-block;
            width: 100%;
            margin-top: 5mm;
        }

        .radio {
            position: relative;
            display: inline-block;
            margin-right: 5mm;
        }

        .radio label {
            display: inline-block;
            margin-top: 0;
            font-size: 0.75rem;
            font-weight: 400;
            margin-top: -0.5mm;
            margin-right: 1mm;
        }
    </style></head><body>

<div class="allergy-result">

    <h2 class="header">
        Uitslag online zelftest op allesoverallergie.nl: {{$score}} / 24
    </h2>

    <div class="result-text">
        {{$results}}
    </div>

    <!-- <hr> -->

    <div class="survey__step" id="step1">
        <h3 class="light">Hoe vaak heb je in het afgelopen jaar last gehad van de volgende klachten?</h3>
        <table><thead><tr><th width="80%"></th><th>Zelden of nooit</th><th>Regelmatig of vaak</th></tr></thead><tbody><tr><td>Jeukende neus</td><td>
                    <input type="radio" name="Q1_A" {{$answers['Q1_A'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q1_A" {{$answers['Q1_A'] == 2 ? 'checked' : ''}}>
                </td></tr><tr><td>Niezen, niesbuien</td><td>
                    <input type="radio" name="Q1_B" {{$answers['Q1_B'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q1_B" {{$answers['Q1_B'] == 1 ? 'checked' : ''}}>
                </td></tr><tr><td>Waterige loopneus</td><td>
                    <input type="radio" name="Q1_C" {{$answers['Q1_C'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q1_C" {{$answers['Q1_C'] == 1 ? 'checked' : ''}}>
                </td></tr><tr><td>Verstopte neus</td><td>
                    <input type="radio" name="Q1_D" {{$answers['Q1_D'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q1_D" {{$answers['Q1_D'] == 1 ? 'checked' : ''}}>
                </td></tr><tr><td>Jeukende, branderige, rode ogen</td><td>
                    <input type="radio" name="Q1_E" {{$answers['Q1_E'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q1_E" {{$answers['Q1_E'] == 1 ? 'checked' : ''}}>
                </td></tr></tbody></table>
    </div>


    <div class="survey__step" id="step2">
        <h3 class="light">Deze allergieklachten verergeren of ervaar je vooral …</h3>
        <table><thead><tr><th width="80%"></th><th>Nee</th><th>Ja</th></tr></thead><tbody><tr><td>… in het voorjaar of in de zomer?</td><td>
                    <input type="radio" name="Q2_A" {{$answers['Q2_A'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_A" {{$answers['Q2_A'] == 3 ? 'checked' : ''}}>
                </td></tr><tr><td>… in de buurt van grasvelden en/of bomen?</td><td>
                    <input type="radio" name="Q2_B" {{$answers['Q2_B'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_B" {{$answers['Q2_B'] == 5 ? 'checked' : ''}}>
                </td></tr><tr><td>… in de buurt van dieren (kat, hond, paard etc.)?</td><td>
                    <input type="radio" name="Q2_C" {{$answers['Q2_C'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_C" {{$answers['Q2_C'] == 3 ? 'checked' : ''}}>
                </td></tr><tr><td>… in de nacht, wanneer je op bed ligt?</td><td>
                    <input type="radio" name="Q2_D" {{$answers['Q2_D'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_D" {{$answers['Q2_D'] == 1 ? 'checked' : ''}}>
                </td></tr><tr><td>… in een ruimte met vloerkleden of vloerbedekking?</td><td>
                    <input type="radio" name="Q2_E" {{$answers['Q2_E'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_E" {{$answers['Q2_E'] == 2 ? 'checked' : ''}}>
                </td></tr><tr><td>… wanneer je bepaalde voedingsmiddelen eet?</td><td>
                    <input type="radio" name="Q2_F" {{$answers['Q2_F'] == 0 ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q2_F" {{$answers['Q2_F'] == 2 ? 'checked' : ''}}>
                </td></tr></tbody></table>
    </div>


    <div class="survey__step" id="step3">
        <h3 class="light">Als je last hebt van klachten, hoeveel last ervaar je hiervan gedurende de dag?</h3>
        <p>Geef op de balk aan in welke mate de klachten van invloed zijn door de knop naar rechts of naar links te bewegen. Hoe hoger de waarde hoe meer last wordt ervaren. </p>
        <div class="radiobuttons">
            <span class="radio"><label>0</label><input type="radio" {{$answers['Q3'] == 0 ? 'checked' : ''}}></span>
            <span class="radio"><label>1</label><input type="radio" {{$answers['Q3'] == 1 ? 'checked' : ''}}></span>
            <span class="radio"><label>2</label><input type="radio" {{$answers['Q3'] == 2 ? 'checked' : ''}}></span>
            <span class="radio"><label>3</label><input type="radio" {{$answers['Q3'] == 3 ? 'checked' : ''}}></span>
            <span class="radio"><label>4</label><input type="radio" {{$answers['Q3'] == 4 ? 'checked' : ''}}></span>
            <span class="radio"><label>5</label><input type="radio" {{$answers['Q3'] == 5 ? 'checked' : ''}}></span>
            <span class="radio"><label>6</label><input type="radio" {{$answers['Q3'] == 6 ? 'checked' : ''}}></span>
            <span class="radio"><label>7</label><input type="radio" {{$answers['Q3'] == 7 ? 'checked' : ''}}></span>
            <span class="radio"><label>8</label><input type="radio" {{$answers['Q3'] == 8 ? 'checked' : ''}}></span>
            <span class="radio"><label>9</label><input type="radio" {{$answers['Q3'] == 9 ? 'checked' : ''}}></span>
            <span class="radio"><label>10</label><input type="radio" {{$answers['Q3'] == 10 ? 'checked' : ''}}></span>
        </div>
    </div>


    <div class="survey__step" id="step4">
        <h3 class="light">Is er in het verleden door een arts een allergie vastgesteld voor:</h3>
        <table><thead><tr><th width="80%"></th><th>Nee</th><th>Ja</th></tr></thead><tbody><tr><td>Gras- en/of boompollen?</td><td>
                    <input type="radio" name="Q4_A" {{$answers['Q4_A'] == 'no' ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q4_A" {{$answers['Q4_A'] == 'yes' ? 'checked' : ''}}>
                </td></tr><tr><td>Huisstofmijt?</td><td>
                    <input type="radio" name="Q4_B" {{$answers['Q4_B'] == 'no' ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q4_B" {{$answers['Q4_B'] == 'yes' ? 'checked' : ''}}>
                </td></tr><tr><td>Dieren?</td><td>
                    <input type="radio" name="Q4_C" {{$answers['Q4_C'] == 'no' ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q4_C" {{$answers['Q4_C'] == 'yes' ? 'checked' : ''}}>
                </td></tr><tr><td>Voedingsmiddelen?</td><td>
                    <input type="radio" name="Q4_D" {{$answers['Q4_D'] == 'no' ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q4_D" {{$answers['Q4_D'] == 'yes' ? 'checked' : ''}}>
                </td></tr><tr><td>Andere allergie?</td><td>
                    <input type="radio" name="Q4_E" {{$answers['Q4_E'] == 'no' ? 'checked' : ''}}>
                </td><td>
                    <input type="radio" name="Q4_E" {{$answers['Q4_E'] == 'yes' ? 'checked' : ''}}>
                </td></tr></tbody></table>
    </div>



</div>

<div class="betternet-wrapper"></div></body></html>
