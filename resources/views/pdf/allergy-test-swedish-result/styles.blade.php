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
    }

    .question-wrapper .question-item .question {
        font-size: 12px;
    }

    @if($domain === \App\Models\User::KLARIFY_US_STORE)
        h2, h3 {
            color: #79C0B7;
        }
        .question-wrapper .question-header {
            color: #79C0B7;
        }
    @else
        h2, h3 {
            color: #FF6651;
        }
        .question-wrapper .question-header {
            color: #FF6651;
        }
    @endif
</style>
