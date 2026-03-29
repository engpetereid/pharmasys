@extends('layouts.admin')
@section('title', 'م \بيتر عيد')

@section('style')
    <style>
        .profile-wrapper {
            font-family: system-ui, -apple-system, sans-serif;
            color: #1f2937;
            max-width: 1100px;
            margin: 0 auto;
            padding: 60px 20px;
            direction: ltr;
        }
        .profile-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #111827;
        }
        .profile-header h1 span {
            color: #4f46e5;
        }
        .profile-header p {
            font-size: 1.125rem;
            color: #4b5563;
            line-height: 1.6;
            max-width: 700px;
            margin: 0 auto;
        }

        .profile-grid {
            display: flex;
            gap: 40px;
            flex-wrap: wrap;
        }
        .col-left {
            flex: 1 1 500px;
        }
        .custom-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4f46e5;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .section-dot {
            width: 8px;
            height: 8px;
            background-color: #4f46e5;
            border-radius: 50%;
            margin-right: 10px;
        }

        .contact-row {
            display: flex;
            align-items: center;
            text-decoration: none;
            padding: 15px;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.2s ease;
            background-color: #fff;
        }
        .contact-row:hover {
            border-color: #4f46e5;
            background-color: #eef2ff;
            transform: translateY(-2px);
        }

        .icon-box {
            width: 45px;
            height: 45px;
            background-color: #e0e7ff;
            color: #4f46e5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-box svg, .cta-icon svg, .arrow-icon svg {
            width: 24px;
            height: 24px;
            display: block;
        }

        .text-group {
            margin-left: 15px;
        }
        .label-text {
            display: block;
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }
        .value-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
        }


        .hero-card h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        .hero-card p {
            color: #c7d2fe;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .arrow-icon {
            margin-left: auto;
            color: #9ca3af;
        }
    </style>
@endsection

@section('content')


    <div class="profile-wrapper">

        <div class="profile-grid">

            <div class="col-left">

                <div class="custom-card">
                    <div class="section-title">
                        <span class="section-dot"></span> Eng/ peter eid
                    </div>

                    <a href="tel:01271970828" class="contact-row">
                        <div class="icon-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        </div>
                        <div class="text-group">
                            <span class="label-text">Primary</span>
                            <span class="value-text">0127 197 0828</span>
                        </div>
                    </a>

                    <a href="tel:01141622348" class="contact-row">
                        <div class="icon-box">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="text-group">
                            <span class="label-text">Secondary</span>
                            <span class="value-text">0114 162 2348</span>
                        </div>
                    </a>
                </div>

                <div class="custom-card">
                    <div class="section-title">
                        <span class="section-dot"></span> Professional Presence
                    </div>

                    <a href="https://www.linkedin.com/in/peter-eid-449a2620b" target="_blank" class="contact-row">
                        <div class="icon-box" style="background-color: #eff6ff; color: #2563eb;">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        </div>
                        <div class="text-group">
                            <span class="value-text">LinkedIn Profile</span>
                            <span class="label-text" style="text-transform: none;">History, endorsements & articles</span>
                        </div>
                        <div class="arrow-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </a>

                    <a href="https://www.facebook.com/share/1SW8a1oxQW/" target="_blank" class="contact-row">
                        <div class="icon-box" style="background-color: #eff6ff; color: #1d4ed8;">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/></svg>
                        </div>
                        <div class="text-group">
                            <span class="value-text">Facebook</span>
                            <span class="label-text" style="text-transform: none;">Community engagement</span>
                        </div>
                        <div class="arrow-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
