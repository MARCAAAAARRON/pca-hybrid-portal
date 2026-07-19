<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCA Bohol - Hybridization Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('images/PCA_Logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0
        }

        :root {
            --green-900: #028c42;
            --green-800: #0b9e4f;
            --green-700: #028c42;
            --green-600: #10b981;
            --green-500: #34d399;
            --green-400: #6ee7b7;
            --yellow: #dfed1f;
            --yellow-dark: #c5d11b;
            --surface: #f0fdf4;
            --white: #ffffff;
            --text: #028c42;
            --text-muted: #4a6650;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px
        }

        body {
            font-family: 'Sora', sans-serif;
            background: var(--white);
            color: var(--text);
            overflow-x: hidden
        }

        /* ── NAV ── */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all .4s ease;
            background: transparent;
        }

        nav.scrolled {
            background: rgba(2, 140, 66, .95);
            backdrop-filter: blur(16px);
            padding: .9rem 2rem;
            box-shadow: 0 4px 32px rgba(0, 0, 0, .15);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: .75rem
        }

        .nav-logo-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .6rem;
            color: var(--green-700);
            line-height: 1.2;
            text-align: center;
            padding: .25rem;
        }

        .nav-brand p {
            color: rgba(255, 255, 255, .95);
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.1
        }

        .nav-brand span {
            color: var(--yellow);
            font-size: .65rem;
            font-weight: 600;
            letter-spacing: .15em;
            text-transform: uppercase
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem
        }

        .nav-links a {
            color: rgba(255, 255, 255, .8);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            transition: color .2s
        }

        .nav-links a:hover {
            color: var(--yellow)
        }

        .btn-nav {
            background: var(--yellow);
            color: var(--green-900);
            padding: .6rem 1.5rem;
            border-radius: 100px;
            font-weight: 700;
            font-size: .875rem;
            text-decoration: none;
            transition: all .2s;
            display: inline-block;
        }

        .btn-nav:hover {
            background: var(--yellow-dark);
            transform: translateY(-1px)
        }

        /* Mobile hamburger */
        .hamburger {
            background: var(--yellow);
            border-radius: 6px; 
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: .25rem
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--green-900);
            border-radius: 2px;
            transition: all .3s
        }

        .mobile-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: rgba(10, 46, 18, .97);
            backdrop-filter: blur(16px);
            padding: 1.5rem 2rem;
            flex-direction: column;
            gap: 1rem;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .mobile-menu a {
            color: rgba(255, 255, 255, .85);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            padding: .5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, .06)
        }

        .mobile-menu .btn-nav {
            margin-top: .5rem;
            text-align: center;
            display: block
        }

        .mobile-menu.open {
            display: flex
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--green-900) 0%, var(--green-700) 45%, var(--green-600) 100%);
        }

        .hero-pattern {
            position: absolute;
            inset: 0;
            opacity: .07;
            background-image: radial-gradient(circle, #fff 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .hero-glow {
            position: absolute;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(45, 158, 66, .25) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .hero-leaves {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none
        }

        .leaf {
            position: absolute;
            opacity: .1;
            font-size: 6rem;
            line-height: 1
        }

        .leaf-1 {
            top: 8%;
            left: 4%;
            transform: rotate(-20deg);
            font-size: 9rem
        }

        .leaf-2 {
            top: 18%;
            right: 6%;
            transform: rotate(15deg);
            font-size: 5rem
        }

        .leaf-3 {
            bottom: 28%;
            left: 2%;
            transform: rotate(10deg)
        }

        .leaf-4 {
            bottom: 12%;
            right: 4%;
            transform: rotate(-30deg);
            font-size: 8rem
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            padding: 2rem;
            max-width: 860px;
        }

        .hero-badges {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2.5rem
        }

        .hero-badge {
            width: 80px;
            height: 80px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .2);
            font-weight: 800;
            font-size: .65rem;
            color: var(--green-700);
            text-align: center;
            line-height: 1.25;
            padding: .5rem;
        }

        /* Swap font-weight:800 text for actual logos: */
        /* .hero-badge img { width:50px; height:50px; object-fit:contain; } */
        .hero-eyebrow {
            display: inline-block;
            background: rgba(245, 226, 0, .15);
            border: 1px solid rgba(245, 226, 0, .3);
            color: var(--yellow);
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .2em;
            text-transform: uppercase;
            padding: .4rem 1.2rem;
            border-radius: 100px;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 6vw, 5rem);
            color: var(--white);
            line-height: 1.05;
            margin-bottom: 1rem;
            letter-spacing: -.02em;
        }

        .hero h1 em {
            color: var(--yellow);
            font-style: normal
        }

        .hero-sub {
            color: rgba(255, 255, 255, .75);
            font-size: 1.1rem;
            max-width: 560px;
            margin: 0 auto 2.5rem;
            font-weight: 400;
            line-height: 1.7;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap
        }

        .btn-primary {
            background: var(--yellow);
            color: var(--green-900);
            padding: .9rem 2.5rem;
            border-radius: 100px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            transition: all .25s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            box-shadow: 0 4px 20px rgba(245, 226, 0, .35);
        }

        .btn-primary:hover {
            background: var(--yellow-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(245, 226, 0, .4)
        }

        .btn-ghost {
            border: 1.5px solid rgba(255, 255, 255, .4);
            color: var(--white);
            padding: .9rem 2.5rem;
            border-radius: 100px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: all .25s;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            backdrop-filter: blur(8px);
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, .1);
            border-color: rgba(255, 255, 255, .7)
        }

        .hero-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 3;
            line-height: 0
        }

        /* ── STATS ── */
        .stats {
            background: var(--green-800);
            padding: 4rem 2rem
        }

        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }

        .stat-num {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--yellow);
            line-height: 1;
            margin-bottom: .35rem
        }

        .stat-label {
            font-size: .75rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .6);
            letter-spacing: .15em;
            text-transform: uppercase
        }

        /* ── SHARED SECTION STYLES ── */
        section {
            padding: 6rem 2rem
        }

        .section-inner {
            max-width: 1100px;
            margin: 0 auto
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(33, 122, 48, .1);
            color: var(--green-500);
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            padding: .4rem 1rem;
            border-radius: 100px;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(33, 122, 48, .15);
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.1;
            letter-spacing: -.02em;
            color: var(--green-900);
            margin-bottom: 1rem;
        }

        .section-desc {
            color: var(--text-muted);
            font-size: 1.05rem;
            max-width: 520px;
            line-height: 1.7
        }

        /* ── FEATURES ── */
        .features {
            background: var(--surface)
        }

        .features-header {
            text-align: center;
            margin-bottom: 4rem
        }

        .features-header .section-desc {
            margin: 0 auto
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem
        }

        .feature-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2.25rem;
            border: 1.5px solid rgba(10, 46, 18, .07);
            transition: all .3s;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--yellow);
            transform: scaleX(0);
            transition: transform .3s;
            transform-origin: left;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px rgba(10, 46, 18, .12);
            border-color: rgba(10, 46, 18, .15)
        }

        .feature-card:hover::before {
            transform: scaleX(1)
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: rgba(33, 122, 48, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            transition: background .3s;
        }

        .feature-card:hover .feature-icon {
            background: var(--yellow)
        }

        .feature-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: .75rem
        }

        .feature-card p {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: .9rem
        }

        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            margin-top: 1.25rem;
            font-size: .75rem;
            font-weight: 600;
            color: var(--green-500);
            background: rgba(33, 122, 48, .08);
            padding: .3rem .85rem;
            border-radius: 100px;
        }

        /* ── HOW IT WORKS ── */
        .hiw {
            background: var(--white)
        }

        .hiw-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center
        }

        .hiw-steps {
            display: flex;
            flex-direction: column
        }

        .step {
            display: flex;
            gap: 1.5rem;
            padding: 2rem 0;
            position: relative
        }

        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 23px;
            top: 64px;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, rgba(33, 122, 48, .2), rgba(33, 122, 48, .03));
        }

        .step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--green-900);
            color: var(--yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: .9rem;
            flex-shrink: 0;
            box-shadow: 0 4px 16px rgba(10, 46, 18, .2);
        }

        .step-content h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: .4rem
        }

        .step-content p {
            color: var(--text-muted);
            font-size: .9rem;
            line-height: 1.65
        }

        .hiw-visual {
            background: linear-gradient(135deg, var(--green-800), var(--green-600));
            border-radius: 28px;
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .hiw-visual::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(245, 226, 0, .07);
            pointer-events: none;
        }

        .mini-card {
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 14px;
            padding: 1.1rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all .3s;
        }

        .mini-card:hover {
            background: rgba(255, 255, 255, .17);
            transform: translateX(4px)
        }

        .mini-card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .mini-card-text p {
            color: rgba(255, 255, 255, .6);
            font-size: .72rem;
            margin-bottom: .1rem
        }

        .mini-card-text strong {
            color: var(--white);
            font-size: .875rem;
            font-weight: 600
        }

        .mini-card-stat {
            margin-left: auto;
            text-align: right
        }

        .mini-card-stat span {
            color: var(--yellow);
            font-weight: 700;
            font-size: 1.1rem;
            display: block
        }

        .mini-card-stat small {
            color: rgba(255, 255, 255, .45);
            font-size: .7rem
        }

        /* ── ABOUT ── */
        .about {
            background: var(--surface)
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5rem;
            align-items: center
        }

        .about-visual {
            border-radius: 28px;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--green-700);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .about-visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .about-visual-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: var(--white);
            padding: 2rem;
            background: var(--green-700);
        }

        .about-visual-content .num {
            font-size: 5.5rem;
            font-weight: 800;
            color: var(--yellow);
            line-height: 1;
            font-family: 'Sora', sans-serif
        }

        .about-visual-content p {
            font-size: 1rem;
            font-weight: 500;
            opacity: .8;
            margin-top: .5rem
        }

        .about-tags {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
            margin-top: 2rem
        }

        .tag {
            background: rgba(33, 122, 48, .08);
            border: 1px solid rgba(33, 122, 48, .12);
            color: var(--green-600);
            font-size: .8rem;
            font-weight: 600;
            padding: .4rem 1rem;
            border-radius: 100px;
        }

        /* ── CTA ── */
        .cta-section {
            background: var(--green-900);
            padding: 7rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            width: 900px;
            height: 900px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 226, 0, .05) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
        }

        .cta-inner {
            position: relative;
            z-index: 2
        }

        .cta-section h2 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            color: var(--white);
            margin-bottom: 1.25rem;
            letter-spacing: -.02em
        }

        .cta-section h2 em {
            color: var(--yellow);
            font-style: normal
        }

        .cta-section p {
            color: rgba(255, 255, 255, .65);
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto 2.5rem;
            line-height: 1.7
        }

        .cta-contact-cards {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 3rem
        }

        .contact-card {
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 16px;
            padding: 1.1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            color: rgba(255, 255, 255, .75);
            font-size: .875rem;
            backdrop-filter: blur(8px);
            text-decoration: none;
            transition: background .2s;
        }

        .contact-card:hover {
            background: rgba(255, 255, 255, .1)
        }

        .contact-card-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(245, 226, 0, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .contact-card strong {
            display: block;
            color: var(--white);
            font-size: .9rem
        }

        .contact-card span {
            font-size: .75rem;
            color: rgba(255, 255, 255, .5)
        }

        /* ── FOOTER ── */
        footer {
            background: #050f07;
            padding: 5rem 2rem 2rem
        }

        .footer-inner {
            max-width: 1100px;
            margin: 0 auto
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 4rem;
            margin-bottom: 4rem
        }

        .footer-logo-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1rem
        }

        .footer-brand p {
            color: rgba(255, 255, 255, .4);
            font-size: .875rem;
            line-height: 1.75
        }

        .footer-col h5 {
            color: var(--yellow);
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .2em;
            text-transform: uppercase;
            margin-bottom: 1.25rem;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: .75rem
        }

        .footer-col ul li a {
            color: rgba(255, 255, 255, .45);
            text-decoration: none;
            font-size: .875rem;
            transition: color .2s
        }

        .footer-col ul li a:hover {
            color: var(--white)
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, .06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: rgba(255, 255, 255, .22);
            font-size: .8rem;
            flex-wrap: wrap;
            gap: .75rem;
        }

        .footer-bottom-links {
            display: flex;
            gap: 1.5rem
        }

        .footer-bottom-links a {
            color: rgba(255, 255, 255, .22);
            text-decoration: none;
            transition: color .2s
        }

        .footer-bottom-links a:hover {
            color: rgba(255, 255, 255, .5)
        }

        /* ── SCROLL ANIMATIONS ── */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .7s ease, transform .7s ease
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0)
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .animate-hero>* {
            animation: fadeUp .85s ease forwards;
            opacity: 0
        }

        .animate-hero>*:nth-child(1) {
            animation-delay: .1s
        }

        .animate-hero>*:nth-child(2) {
            animation-delay: .25s
        }

        .animate-hero>*:nth-child(3) {
            animation-delay: .4s
        }

        .animate-hero>*:nth-child(4) {
            animation-delay: .55s
        }

        .animate-hero>*:nth-child(5) {
            animation-delay: .7s
        }

        /* ── ORG CHART ── */
        .org-section {
            background: var(--white);
        }

        .org-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .org-header .section-desc {
            margin: 0 auto;
        }

        .org-tree {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
        }

        .org-level {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            position: relative;
        }

        .org-connector {
            width: 2px;
            height: 2.5rem;
            background: linear-gradient(to bottom, var(--green-500), var(--green-400));
            margin: 0 auto;
        }

        .org-h-line {
            position: relative;
            display: flex;
            justify-content: center;
            margin-bottom: 0;
        }

        .org-h-line::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: min(70%, 600px);
            height: 2px;
            background: var(--green-400);
        }

        .org-branch-connectors {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .org-branch-line {
            width: 2px;
            height: 2rem;
            background: var(--green-400);
        }

        .org-card {
            background: var(--surface);
            border: 1.5px solid rgba(10, 46, 18, .08);
            border-radius: 20px;
            padding: 2rem 1.75rem;
            text-align: center;
            width: 240px;
            transition: all .3s;
            position: relative;
        }

        .org-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(10, 46, 18, .1);
        }

        .org-card.org-head {
            width: 280px;
            border-color: var(--green-500);
            background: linear-gradient(135deg, rgba(16, 185, 129, .06), rgba(52, 211, 153, .04));
        }

        .org-card.org-head .org-avatar {
            width: 90px;
            height: 90px;
            font-size: 2.5rem;
            border: 3px solid var(--green-500);
        }

        .org-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: var(--green-800);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: var(--white);
            border: 2.5px solid rgba(10, 46, 18, .1);
            overflow: hidden;
        }

        .org-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .org-card h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: .2rem;
        }

        .org-card .org-role {
            font-size: .75rem;
            font-weight: 600;
            color: var(--green-600);
            text-transform: uppercase;
            letter-spacing: .1em;
            margin-bottom: .5rem;
        }

        .org-card .org-desc {
            font-size: .8rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .org-placeholder-badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            margin-top: .75rem;
            font-size: .65rem;
            font-weight: 600;
            color: rgba(100, 116, 100, .6);
            background: rgba(10, 46, 18, .04);
            padding: .25rem .65rem;
            border-radius: 100px;
        }

        @media(max-width:768px) {
            .org-level {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }

            .org-h-line::before {
                display: none;
            }

            .org-branch-connectors {
                flex-direction: column;
                align-items: center;
            }

            .org-card {
                width: 100%;
                max-width: 300px;
            }

            .org-card.org-head {
                width: 100%;
                max-width: 300px;
            }
        }

        /* ── FARM ACTIVITY ── */
        .farm-activity {
            background: var(--white);
        }

        .farm-activity-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .year-select-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(11, 158, 79, 0.08);
            border: 1.5px solid var(--green-500);
            color: var(--green-900);
            padding: .5rem 1rem;
            border-radius: 100px;
            font-weight: 600;
            font-size: .875rem;
        }

        .year-select-pill select {
            appearance: none;
            -webkit-appearance: none;
            background: transparent;
            border: none;
            outline: none;
            font-size: .875rem;
            font-weight: 700;
            color: var(--green-900);
            cursor: pointer;
            font-family: 'Sora', sans-serif;
            padding-right: .5rem;
        }

        .farm-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .farm-card {
            background: var(--surface);
            border: 1.5px solid rgba(10, 46, 18, .08);
            border-radius: 20px;
            padding: 2rem;
            transition: all .3s;
            position: relative;
            overflow: hidden;
        }

        .farm-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--green-700), var(--green-500));
        }

        .farm-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 48px rgba(10, 46, 18, .1);
        }

        .farm-card-header {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: 1.5rem;
        }

        .farm-card-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--green-800);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .farm-card-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green-900);
            line-height: 1.2;
        }

        .farm-card-header span {
            font-size: .75rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .farm-stats-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: .75rem;
        }

        .farm-stat-item {
            background: var(--white);
            border-radius: 12px;
            padding: .75rem 1rem;
            border: 1px solid rgba(10, 46, 18, .06);
        }

        .farm-stat-item .num {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--green-800);
            line-height: 1;
        }

        .farm-stat-item .label {
            font-size: .7rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-top: .25rem;
        }

        .farm-card-footer {
            margin-top: 1.25rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(10, 46, 18, .06);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .farm-seednut-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(245, 226, 0, .15);
            color: var(--green-900);
            font-size: .8rem;
            font-weight: 700;
            padding: .35rem .85rem;
            border-radius: 100px;
        }

        .farm-seedling-text {
            font-size: .8rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        @media(max-width:768px) {
            .farm-activity-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .farm-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ── RESPONSIVE ── */
        @media(max-width:768px) {
            .nav-links {
                display: none
            }

            .hamburger {
                display: flex
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem
            }

            .features-grid {
                grid-template-columns: 1fr
            }

            .hiw-grid,
            .about-grid {
                grid-template-columns: 1fr
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2.5rem
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center
            }

            section {
                padding: 4rem 1.25rem
            }

            .hero h1 {
                font-size: 2.5rem
            }
        }
    </style>
</head>

<body>

    <!-- ═══════════════════════════════════════
     NAVBAR
════════════════════════════════════════ -->
    <nav id="navbar">
        <div class="nav-logo">


            <img src="{{ asset('images/PCA_Logo.png') }}" alt="PCA Logo"
                style="height:40px;width:40px;border-radius:50%;background:#fff;padding:4px;object-fit:contain">

            <div class="nav-brand">
                <p>PCA – Bohol</p>
                <span>Hybridization Portal</span>
            </div>
        </div>

        <div class="nav-links">
            <a href="#farm-activity">Farm Activity</a>
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#about">About</a>
            <a href="#our-team">Our Team</a>
            <a href="/portal/login" class="btn-nav">Login</a>
        </div>

        <!-- Mobile hamburger -->
        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <div class="mobile-menu" id="mobile-menu">
            <a href="#farm-activity">Farm Activity</a>
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#about">About</a>
            <a href="#our-team">Our Team</a>
            <a href="/portal/login" class="btn-nav">Login / Register</a>
        </div>
    </nav>


    <!-- ═══════════════════════════════════════
     HERO
════════════════════════════════════════ -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-pattern"></div>
        <div class="hero-glow"></div>

        <!-- Decorative leaves (replace with actual image if needed) -->
        <div class="hero-leaves">
            <span class="leaf leaf-1">🌴</span>
            <span class="leaf leaf-2">🥥</span>
            <span class="leaf leaf-3">🌿</span>
            <span class="leaf leaf-4">🌴</span>
        </div>

        <!-- Uncomment to use a real background photo instead of the gradient:
  <img src="{{ asset('images/CoconutBackground.png') }}" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.25">
  -->

        <div class="hero-content animate-hero">
            <div class="hero-badges">
                <!-- Replace with real logos if available 
                <div class="hero-badge">DA<br>Phil.</div>
                <div class="hero-badge">PCA<br>Bohol</div>
    -->

                <div class="hero-badge"><img src="{{ asset('images/DA_logo.png') }}" alt="DA"
                        style="width:75px;height:75px;object-fit:contain"></div>
                <div class="hero-badge"><img src="{{ asset('images/PCA_Logo.png') }}" alt="PCA"
                        style="width:75px;height:75px;object-fit:contain"></div>

            </div>

            <h1 class="font-bold">PCA Bohol<br><em>Hybridization Portal</em></h1>
            <p class="hero-sub">A secure Role-Based Algorithm System managing coconut hybridization activities across
                allfield sites.</p>
            <div class="hero-cta">
                <a href="#features" class="btn-primary">View System Scope ↓</a>
                <a href="/portal/login" class="btn-ghost">Access Portal</a>
            </div>
        </div>

        <div class="hero-wave">
            <svg viewBox="0 0 1440 90" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
                style="width:100%;display:block">
                <path d="M0,60 C360,100 720,20 1080,60 C1260,80 1380,50 1440,40 L1440,90 L0,90 Z" fill="#0b9e4f" />
            </svg>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     STATS BAR
════════════════════════════════════════ -->
    <div class="stats">
        <div class="stats-grid">
            <div class="reveal">
                <div class="stat-num">{{ $siteCount }}</div>
                <div class="stat-label">Field Sites</div>
            </div>
            <div class="reveal">
                <div class="stat-num">{{ number_format($totalSeednuts) }}</div>
                <div class="stat-label">Seednuts Harvested</div>
            </div>
            <div class="reveal">
                <div class="stat-num">{{ number_format($totalSeedlings) }}</div>
                <div class="stat-label">Seedlings Distributed</div>
            </div>
            <div class="reveal">
                <div class="stat-num">{{ $totalHarvests + $totalPollen + $totalDistribution }}</div>
                <div class="stat-label">Reports Filed ({{ $year }})</div>
            </div>
        </div>
    </div>


    <!-- ═══════════════════════════════════════
     FARM ACTIVITY
════════════════════════════════════════ -->
    <section class="farm-activity" id="farm-activity">
        <div class="section-inner">
            <div class="farm-activity-header">
                <div>
                    <div class="section-tag reveal">✦ Live Program Data</div>
                    <h2 class="section-title font-bold reveal">Farm Activity<br>per Field Site</h2>
                    <p class="section-desc reveal">Real-time operational data from all PCA Bohol hybridization field
                        sites — open for public transparency.</p>
                </div>
                <div class="year-select-pill reveal">
                    📅
                    <select onchange="window.location.href='/?year='+this.value">
                        @for($y = now()->year; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    ▾
                </div>
            </div>

            <div class="farm-grid">
                @foreach($sites as $site)
                    <div class="farm-card reveal">
                        <div class="farm-card-header">
                            <div class="farm-card-icon">🌴</div>
                            <div>
                                <h3>{{ $site['name'] }}</h3>
                                <span>{{ $year }} Data</span>
                            </div>
                        </div>
                        <div class="farm-stats-row">
                            <div class="farm-stat-item">
                                <div class="num">{{ $site['harvests'] }}</div>
                                <div class="label">Harvest Reports</div>
                            </div>
                            <div class="farm-stat-item">
                                <div class="num">{{ $site['pollen'] }}</div>
                                <div class="label">Pollen Records</div>
                            </div>
                            <div class="farm-stat-item">
                                <div class="num">{{ $site['nursery'] }}</div>
                                <div class="label">Nursery Ops</div>
                            </div>
                            <div class="farm-stat-item">
                                <div class="num">{{ $site['distribution'] }}</div>
                                <div class="label">Distributions</div>
                            </div>
                        </div>
                        <div class="farm-card-footer">
                            <div class="farm-seednut-badge">🥥 {{ number_format($site['seednuts']) }} seednuts</div>
                            <div class="farm-seedling-text">🌱 {{ number_format($site['seedlings']) }} seedlings</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     FEATURES
════════════════════════════════════════ -->
    <section class="features" id="features">
        <div class="section-inner">
            <div class="features-header">
                <div class="section-tag reveal">✦ Project Scope</div>
                <h2 class="section-title font-bold reveal">The Role-Based<br>Algorithm System</h2>
                <p class="section-desc reveal">Centralizing PCA coconut hybridization records through structured data
                    isolation and multi-level validation.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon">🧪</div>
                    <h3>Pollen & Harvest</h3>
                    <p>Implementing carry-forward algorithms for monthly seednut and pollen production across all sites.
                    </p>
                    <div class="feature-badge">✓ Automated Balances</div>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon">🌴</div>
                    <h3>Nursery & Hybridization</h3>
                    <p>Full lifecycle tracking of hybrid seedlings with batch-level germination and distribution
                        details.</p>
                    <div class="feature-badge">✓ Batch Governance</div>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon">📋</div>
                    <h3>Official Reporting</h3>
                    <p>Generating PCA-branded Excel and PDF reports with automated digital signature embedding.</p>
                    <div class="feature-badge">✓ PCA Formatted</div>
                </div>

            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════════ -->
    <section class="hiw" id="how-it-works">
        <div class="section-inner">
            <div class="hiw-grid">
                <!-- Left: Steps -->
                <div>
                    <div class="section-tag reveal">✦ System Workflow</div>
                    <h2 class="section-title font-bold reveal">The 4-Stage<br>Approval Workflow</h2>
                    <p class="section-desc reveal" style="margin-bottom:2.5rem">Enforcing strict accountability through
                        a progressive role-based validation algorithm.</p>

                    <div class="hiw-steps">
                        <div class="step reveal">
                            <div class="step-num">01</div>
                            <div class="step-content">
                                <h4>Draft & Prepare</h4>
                                <p>Supervisors record field-level data isolation. Records are initialized as Drafts
                                    before being Prepared for review.</p>
                            </div>
                        </div>
                        <div class="step reveal">
                            <div class="step-num">02</div>
                            <div class="step-content">
                                <h4>Review & Validate</h4>
                                <p>Managers perform comparative analysis across sites and validate the integrity of
                                    submitted records.</p>
                            </div>
                        </div>
                        <div class="step reveal">
                            <div class="step-num">03</div>
                            <div class="step-content">
                                <h4>Track &amp; Report</h4>
                                <p>Monitor progress in real time, access reports, and coordinate with PCA
                                    officers seamlessly.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Dashboard preview cards -->
                <div class="hiw-visual reveal">
                    <div class="mini-card">
                        <div class="mini-card-icon">🌱</div>
                        <div class="mini-card-text">
                            <p>New seedlings registered</p>
                            <strong>Batch #BH-2024-042</strong>
                        </div>
                        <div class="mini-card-stat"><span>248</span><small>this month</small></div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-card-icon">✅</div>
                        <div class="mini-card-text">
                            <p>Hybridization success rate</p>
                            <strong>Variety: Dwarf × Tall</strong>
                        </div>
                        <div class="mini-card-stat"><span>94%</span><small>avg rate</small></div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-card-icon">📍</div>
                        <div class="mini-card-text">
                            <p>Active field officers</p>
                            <strong>Province-wide coverage</strong>
                        </div>
                        <div class="mini-card-stat"><span>38</span><small>officers</small></div>
                    </div>
                    <div class="mini-card">
                        <div class="mini-card-icon">📋</div>
                        <div class="mini-card-text">
                            <p>Reports generated today</p>
                            <strong>Q2 Progress Summary</strong>
                        </div>
                        <div class="mini-card-stat"><span>12</span><small>reports</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     ABOUT
════════════════════════════════════════ -->
    <section class="about" id="about">
        <div class="section-inner">
            <div class="about-grid">
                <!-- Left: Visual -->
                <div class="about-visual reveal">
                    <!-- Replace with a real photo: -->
                    <img src="{{ asset('images/pca_main_banner1.png') }}" alt="PCA Bohol">

                    <div class="about-visual-content">
                        <div class="num">2019</div>
                        <p>Serving Bohol's coconut<br>farmers since</p>
                    </div>
                </div>

                <!-- Right: Text -->
                <div>
                    <div class="section-tag reveal">✦ About the Program</div>
                    <h2 class="section-title font-bold reveal">Securing the future<br>of Bohol's coconut industry</h2>
                    <p class="reveal" style="color:var(--text-muted);line-height:1.75;font-size:.95rem">
                        The Philippine Coconut Authority (PCA) Bohol oversees the hybridization program aimed at
                        increasing coconut productivity across all municipalities of Bohol. Through innovation and
                        dedicated field support, we empower every local coconut farmer.
                    </p>
                    <p class="reveal" style="color:var(--text-muted);line-height:1.75;font-size:.95rem;margin-top:1rem">
                        Our hybridization portal digitizes the entire program — from farmer enrollment to seedling
                        monitoring — ensuring transparency, accuracy, and accessibility for all stakeholders.
                    </p>
                    <div class="about-tags reveal">
                        <span class="tag">🥥 Dwarf Variety</span>
                        <span class="tag">🌴 Tall Variety</span>
                        <span class="tag">🔬 Makapuno Hybrid</span>
                        <span class="tag">📋 LGU Certified</span>
                        <span class="tag">🇵🇭 DA–Philippines</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     ORGANIZATIONAL STRUCTURE
════════════════════════════════════════ -->
    <section class="org-section" id="our-team">
        <div class="section-inner">
            <div class="org-header">
                <div class="section-tag reveal">✦ Leadership</div>
                <h2 class="section-title font-bold reveal">Organizational<br>Structure</h2>
                <p class="section-desc reveal">The dedicated team behind PCA Bohol's coconut hybridization program.</p>
            </div>

            <div class="org-tree">
                <!-- Top: Provincial Manager -->
                <div class="org-level reveal">
                    <div class="org-card org-head">
                        <div class="org-avatar">👤</div>
                        <h4>Juan Dela Cruz</h4>
                        <div class="org-role">Provincial Manager</div>
                        <div class="org-desc">Oversees all PCA Bohol operations, programs, and field activities.</div>
                        <div class="org-placeholder-badge">📷 Photo placeholder</div>
                    </div>
                </div>

                <!-- Connector line -->
                <div class="org-connector"></div>

                <!-- Horizontal branch line -->
                <div class="org-h-line" style="width:100%;height:2rem;"></div>

                <!-- Second tier -->
                <div class="org-level reveal" style="gap:3rem;">
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div class="org-branch-line"></div>
                        <div class="org-card">
                            <div class="org-avatar">👤</div>
                            <h4>Maria Santos</h4>
                            <div class="org-role">Section Head – Hybridization</div>
                            <div class="org-desc">Leads the hybridization research and seednut production programs.
                            </div>
                            <div class="org-placeholder-badge">📷 Photo placeholder</div>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div class="org-branch-line"></div>
                        <div class="org-card">
                            <div class="org-avatar">👤</div>
                            <h4>Pedro Reyes</h4>
                            <div class="org-role">Section Head – Operations</div>
                            <div class="org-desc">Manages nursery operations, pollen production, and distribution
                                logistics.</div>
                            <div class="org-placeholder-badge">📷 Photo placeholder</div>
                        </div>
                    </div>
                </div>

                <!-- Connector line -->
                <div class="org-connector"></div>
                <div class="org-h-line" style="width:100%;height:2rem;"></div>

                <!-- Third tier: Field Supervisors -->
                <div class="org-level reveal">
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div class="org-branch-line"></div>
                        <div class="org-card">
                            <div class="org-avatar" style="background:var(--green-600);">👤</div>
                            <h4>Ana Lim</h4>
                            <div class="org-role">Field Supervisor</div>
                            <div class="org-desc">Loay Farm</div>
                            <div class="org-placeholder-badge">📷 Photo placeholder</div>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div class="org-branch-line"></div>
                        <div class="org-card">
                            <div class="org-avatar" style="background:var(--green-600);">👤</div>
                            <h4>Carlos Manalo</h4>
                            <div class="org-role">Field Supervisor</div>
                            <div class="org-desc">Balilihan Farm</div>
                            <div class="org-placeholder-badge">📷 Photo placeholder</div>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div class="org-branch-line"></div>
                        <div class="org-card">
                            <div class="org-avatar" style="background:var(--green-600);">👤</div>
                            <h4>Rosa Tan</h4>
                            <div class="org-role">Field Supervisor</div>
                            <div class="org-desc">Additional Site</div>
                            <div class="org-placeholder-badge">📷 Photo placeholder</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     CTA
════════════════════════════════════════ -->
    <section class="cta-section" id="login">
        <div class="cta-inner">
            <div class="section-tag reveal"
                style="justify-content:center;background:rgba(245,226,0,.1);border-color:rgba(245,226,0,.2);color:var(--yellow)">
                ✦ Ready to Join?</div>
            <h2 class="font-bold reveal">Join the program<br><em>today</em></h2>
            <p class="reveal">Register now and become part of Bohol's coconut hybridization initiative. Free for all
                enrolled farmers and field officers.</p>
            <div class="reveal">
                <a href="/portal/login" class="btn-primary" style="font-size:1.05rem;padding:1rem 2.75rem">Login /
                    Register →</a>
            </div>
            <div class="cta-contact-cards">
                <a href="mailto:bohol@pca.gov.ph" class="contact-card reveal">
                    <div class="contact-card-icon">✉️</div>
                    <div><strong>bohol@pca.gov.ph</strong><span>Email Support</span></div>
                </a>
                <a href="tel:0384111234" class="contact-card reveal">
                    <div class="contact-card-icon">📞</div>
                    <div><strong>(038) 411-1234</strong><span>Office Line</span></div>
                </a>
                <div class="contact-card reveal">
                    <div class="contact-card-icon">📍</div>
                    <div><strong>Tagbilaran City, Bohol</strong><span>PCA Bohol Office, 6300</span></div>
                </div>
            </div>
        </div>
    </section>


    <!-- ═══════════════════════════════════════
     FOOTER
════════════════════════════════════════ -->
    <footer>
        <div class="footer-inner">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo-row">
                        <div class="nav-logo-circle"
                            style="background:rgba(255,255,255,.08);color:var(--yellow);font-size:.6rem">PCA<br>DA</div>
                        <!-- Real logos:
          <img src="{{ asset('images/PCA_Logo.png') }}" alt="PCA" style="height:36px;background:#fff;border-radius:50%;padding:3px">
          <img src="{{ asset('images/DA_Logo.png') }}" alt="DA" style="height:36px;background:#fff;border-radius:50%;padding:3px">
          -->
                        <span style="color:var(--white);font-weight:700;font-size:.95rem">Philippine Coconut Authority –
                            Bohol</span>
                    </div>
                    <p>Securing the future of Bohol's coconut industry through innovation, hybridization, and dedicated
                        support for every local farmer.</p>
                </div>

                <div class="footer-col">
                    <h5>Quick Links</h5>
                    <ul>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">How It Works</a></li>
                        <li><a href="/portal/login">Login</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Contact</h5>
                    <ul>
                        <li><a href="mailto:bohol@pca.gov.ph">bohol@pca.gov.ph</a></li>
                        <li><a href="tel:0384111234">(038) 411-1234</a></li>
                        <li><a href="#">Tagbilaran City, Bohol 6300</a></li>
                        <li><a href="#">pca.gov.ph</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <!-- For Laravel Blade: replace 2026 with {{ date('Y') }} -->
                <span>© 2026 Philippine Coconut Authority – Bohol. All Rights Reserved.</span>
                <div class="footer-bottom-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>


    <!-- ═══════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════ -->
    <script>
        // Sticky nav
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 60);
        });

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const mobileMenu = document.getElementById('mobile-menu');
        hamburger.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
        });
        // Close mobile menu on link click
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => mobileMenu.classList.remove('open'));
        });

        // Scroll-triggered reveal animations
        const revealEls = document.querySelectorAll('.reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    // Stagger siblings within the same parent
                    const siblings = [...entry.target.parentElement.querySelectorAll('.reveal')];
                    const idx = siblings.indexOf(entry.target);
                    entry.target.style.transitionDelay = `${idx * 80}ms`;
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => revealObserver.observe(el));
    </script>

</body>

</html>