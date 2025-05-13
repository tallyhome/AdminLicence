@extends('admin.layouts.app')

@section('title', __('email_providers.page_title'))

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ $emailDocContent['title'] ?? __('email_providers.title') }}</h1>
                <div class="language-selector">
                    <form id="language-form" action="{{ route('admin.set.language') }}" method="POST" class="d-inline">
                        @csrf
                        <select class="form-select" name="locale" onchange="document.getElementById('language-form').submit()">
                            @foreach($availableLanguages as $code => $name)
                                <option value="{{ $code }}" {{ $currentLanguage === $code ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="markdown-content">
                        @if(isset($emailDocContent) && !empty($emailDocContent))
                            <div id="markdown-content">
                                <h1>{{ $emailDocContent['title'] ?? __('email_providers.title') }}</h1>
                                <p>{{ $emailDocContent['description'] }}</p>
                                
                                <h2>{{ $emailDocContent['table_of_contents'] }}</h2>
                                <ol>
                                    <li><a href="#introduction">{{ $emailDocContent['introduction']['title'] }}</a></li>
                                    <li><a href="#smtp">{{ $emailDocContent['smtp']['title'] }}</a></li>
                                    <li><a href="#phpmail">{{ $emailDocContent['phpmail']['title'] }}</a></li>
                                    <li><a href="#mailgun">{{ $emailDocContent['mailgun']['title'] }}</a></li>
                                    <li><a href="#mailchimp">{{ $emailDocContent['mailchimp']['title'] }}</a></li>
                                    <li><a href="#rapidmail">{{ $emailDocContent['rapidmail']['title'] }}</a></li>
                                    <li><a href="#comparison">{{ $emailDocContent['comparison']['title'] }}</a></li>
                                    <li><a href="#troubleshooting">{{ $emailDocContent['troubleshooting']['title'] }}</a></li>
                                </ol>
                                
                                <h2 id="introduction">{{ $emailDocContent['introduction']['title'] }}</h2>
                                <p>{{ $emailDocContent['introduction']['description'] }}</p>
                                
                                <h2 id="smtp">{{ $emailDocContent['smtp']['title'] }}</h2>
                                <p>{{ $emailDocContent['smtp']['description'] }}</p>
                                
                                <h3>{{ $emailDocContent['smtp']['configuration'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['smtp']['config_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['smtp']['advantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['smtp']['advantages_list'] as $advantage)
                                        <li>{{ $advantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['smtp']['disadvantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['smtp']['disadvantages_list'] as $disadvantage)
                                        <li>{{ $disadvantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['smtp']['example'] }}</h3>
                                
                                <h2 id="phpmail">{{ $emailDocContent['phpmail']['title'] }}</h2>
                                <p>{{ $emailDocContent['phpmail']['description'] }}</p>
                                
                                <h3>{{ $emailDocContent['phpmail']['configuration'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['phpmail']['config_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['phpmail']['advantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['phpmail']['advantages_list'] as $advantage)
                                        <li>{{ $advantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['phpmail']['disadvantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['phpmail']['disadvantages_list'] as $disadvantage)
                                        <li>{{ $disadvantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['phpmail']['example'] }}</h3>
                                
                                <h2 id="mailgun">{{ $emailDocContent['mailgun']['title'] }}</h2>
                                <p>{{ $emailDocContent['mailgun']['description'] }}</p>
                                
                                <h3>{{ $emailDocContent['mailgun']['configuration'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailgun']['config_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailgun']['advantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailgun']['advantages_list'] as $advantage)
                                        <li>{{ $advantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailgun']['disadvantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailgun']['disadvantages_list'] as $disadvantage)
                                        <li>{{ $disadvantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailgun']['example'] }}</h3>
                                
                                <h2 id="mailchimp">{{ $emailDocContent['mailchimp']['title'] }}</h2>
                                <p>{{ $emailDocContent['mailchimp']['description'] }}</p>
                                
                                <h3>{{ $emailDocContent['mailchimp']['configuration'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailchimp']['config_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailchimp']['advantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailchimp']['advantages_list'] as $advantage)
                                        <li>{{ $advantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailchimp']['disadvantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['mailchimp']['disadvantages_list'] as $disadvantage)
                                        <li>{{ $disadvantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['mailchimp']['example'] }}</h3>
                                
                                <h2 id="rapidmail">{{ $emailDocContent['rapidmail']['title'] }}</h2>
                                <p>{{ $emailDocContent['rapidmail']['description'] }}</p>
                                
                                <h3>{{ $emailDocContent['rapidmail']['configuration'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['rapidmail']['config_items'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['rapidmail']['advantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['rapidmail']['advantages_list'] as $advantage)
                                        <li>{{ $advantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['rapidmail']['disadvantages'] }}</h3>
                                <ul>
                                    @foreach($emailDocContent['rapidmail']['disadvantages_list'] as $disadvantage)
                                        <li>{{ $disadvantage }}</li>
                                    @endforeach
                                </ul>
                                
                                <h3>{{ $emailDocContent['rapidmail']['example'] }}</h3>
                                
                                <h2 id="comparison">{{ $emailDocContent['comparison']['title'] }}</h2>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ $emailDocContent['comparison']['title'] }}</th>
                                            <th>{{ $emailDocContent['comparison']['deliverability'] }}</th>
                                            <th>{{ $emailDocContent['comparison']['price'] }}</th>
                                            <th>{{ $emailDocContent['comparison']['ease_of_setup'] }}</th>
                                            <th>{{ $emailDocContent['comparison']['advanced_features'] }}</th>
                                            <th>{{ $emailDocContent['comparison']['gdpr_compliance'] }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>SMTP</td>
                                            <td>{{ $emailDocContent['comparison']['variable'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['free'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['moderate'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['limited'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['depends'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>PHPMail</td>
                                            <td>{{ $emailDocContent['comparison']['variable'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['free'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['moderate'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['moderate'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['depends'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Mailgun</td>
                                            <td>{{ $emailDocContent['comparison']['high'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['freemium'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['easy'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['numerous'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['good'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Mailchimp</td>
                                            <td>{{ $emailDocContent['comparison']['very_high'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['paid'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['moderate'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['very_numerous'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['good'] }}</td>
                                        </tr>
                                        <tr>
                                            <td>Rapidmail</td>
                                            <td>{{ $emailDocContent['comparison']['high'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['paid'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['easy'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['numerous'] }}</td>
                                            <td>{{ $emailDocContent['comparison']['excellent'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <h2 id="troubleshooting">{{ $emailDocContent['troubleshooting']['title'] }}</h2>
                                <h3>{{ $emailDocContent['troubleshooting']['common_problems'] }}</h3>
                                
                                <h4>{{ $emailDocContent['troubleshooting']['emails_not_sent'] }}</h4>
                                <ul>
                                    @foreach($emailDocContent['troubleshooting']['emails_not_sent_tips'] as $tip)
                                        <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                                
                                <h4>{{ $emailDocContent['troubleshooting']['emails_as_spam'] }}</h4>
                                <ul>
                                    @foreach($emailDocContent['troubleshooting']['emails_as_spam_tips'] as $tip)
                                        <li>{{ $tip }}</li>
                                    @endforeach
                                </ul>
                                
                                <h4>{{ $emailDocContent['troubleshooting']['configuration_issues'] }}</h4>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                {{ __('email_providers.not_available') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .markdown-content h1 { font-size: 2rem; margin-bottom: 1rem; }
    .markdown-content h2 { font-size: 1.75rem; margin-top: 2rem; margin-bottom: 1rem; }
    .markdown-content h3 { font-size: 1.5rem; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .markdown-content h4 { font-size: 1.25rem; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .markdown-content p { margin-bottom: 1rem; }
    .markdown-content ul, .markdown-content ol { margin-bottom: 1rem; padding-left: 2rem; }
    .markdown-content table { width: 100%; margin-bottom: 1rem; border-collapse: collapse; }
    .markdown-content table th, .markdown-content table td { padding: 0.5rem; border: 1px solid #dee2e6; }
    .markdown-content pre { background-color: #f8f9fa; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; overflow-x: auto; }
    .markdown-content code { background-color: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.875em; }
    .markdown-content pre code { padding: 0; background-color: transparent; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/anchor-scroll.js') }}"></script>
@endpush