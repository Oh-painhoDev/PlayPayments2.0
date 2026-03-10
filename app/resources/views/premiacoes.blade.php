@extends('layouts.dashboard')

@section('title', 'Premiações - PlayPayments')

@section('content')
<div class="flex flex-col w-full bg-black relative min-h-screen scrollable-content">

    <!-- Header Decorativo -->
    <div class="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-[#D4AF37]/20 via-transparent to-transparent pointer-events-none"></div>

    <!-- HERO & CAROUSEL (First Screen) -->
    <section class="flex flex-col items-center justify-center min-h-screen text-center px-6 relative">
        <div class="mb-8">
            <h1 class="text-6xl md:text-8xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-r from-[#8a6d1d] via-[#D4AF37] to-[#f5de8a] drop-shadow-[0_0_20px_rgba(212,175,55,0.3)]">
                PREMIAÇÕES
            </h1>
            <p class="text-gray-400 text-lg md:text-2xl max-w-2xl mt-4 font-medium mx-auto">
                Reconhecimento que representa o crescimento da nossa comunidade.
            </p>
        </div>

        <!-- CAROUSEL INFINITO DE IMAGENS -->
        <div class="loop-images w-full relative overflow-hidden py-10">
            <div class="carousel-track-container relative h-[30rem] w-full flex items-center justify-center">
                <div class="carousel-track" style="--time: 60s; --total: 12;">
                    <div class="carousel-item" style="--i: 1;"><img src="https://images.unsplash.com/photo-1758314896569-b3639ee707c4?q=80&w=715&auto=format&fit=crop" alt="award-image"></div>
                    <div class="carousel-item" style="--i: 2;"><img src="https://plus.unsplash.com/premium_photo-1671649240322-2124cd07eaae?q=80&w=627&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 3;"><img src="https://plus.unsplash.com/premium_photo-1673029925648-af80569efc46?q=80&w=687&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 4;"><img src="https://plus.unsplash.com/premium_photo-1666533099824-abd0ed813f2a?q=80&w=687&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 5;"><img src="https://plus.unsplash.com/premium_photo-1671105035554-7f8c2a587201?q=80&w=627&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 6;"><img src="https://plus.unsplash.com/premium_photo-1686750875748-d00684d36b1e?q=80&w=687&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 7;"><img src="https://plus.unsplash.com/premium_photo-1686844462591-393ceae12be0?q=80&w=764&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 8;"><img src="https://plus.unsplash.com/premium_photo-1686839181367-febb561faa53?q=80&w=687&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 9;"><img src="https://plus.unsplash.com/premium_photo-1671199850329-91cae34a6b6d?q=80&w=627&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 10;"><img src="https://plus.unsplash.com/premium_photo-1685655611311-9f801b43b9fa?q=80&w=627&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 11;"><img src="https://plus.unsplash.com/premium_photo-1675598468920-878ae1e46f14?q=80&w=764&auto=format&fit=crop"  alt="award-image"></div>
                    <div class="carousel-item" style="--i: 12;"><img src="https://images.unsplash.com/photo-1718036094878-ecdce2b1be95?q=80&w=715&auto=format&fit=crop"  alt="award-image"></div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <a href="#historia" class="scroll-btn absolute bottom-10 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 no-underline group">
            <span class="text-white/40 group-hover:text-[#D4AF37] text-xs font-bold tracking-[0.3em] uppercase transition-colors">Ver História</span>
            <div class="w-6 h-10 border-2 border-white/20 group-hover:border-[#D4AF37]/50 rounded-full flex justify-center p-1 transition-colors">
                <div class="w-1 h-2 bg-[#D4AF37] rounded-full animate-bounce"></div>
            </div>
        </a>
    </section>

    <!-- CONTEÚDO PRINCIPAL -->
    <div id="historia" class="pt-32"></div>
    <section class="relative py-24 px-6 font-['Poppins']">
        <div class="max-w-6xl mx-auto text-center">
            <p class="text-[#D4AF37] tracking-[0.4em] uppercase mb-4 font-bold text-sm">
                ◆ Reconhecimento e Conquistas ◆
            </p>
            <h2 class="text-5xl md:text-7xl font-black text-white mb-10 tracking-tight leading-tight">
                MARCOS DA NOSSA HISTÓRIA
            </h2>
            <div class="max-w-3xl mx-auto space-y-6 text-gray-400 text-lg md:text-xl leading-relaxed font-normal">
                <p>
                    A trajetória da <strong class="text-white">PlayPayments</strong> simboliza dedicação, estratégia e a confiança de nossa comunidade.
                </p>
                <p>
                    Desde os primeiros <strong class="text-[#D4AF37]">10.000 usuários</strong> até conquistas de <strong class="text-[#D4AF37]">milhões de participantes</strong> que transformam o mercado digital diariamente.
                </p>
            </div>
        </div>
    </section>

    <!-- CARDS DE PREMIAÇÃO -->
    <section class="pb-32 px-6">
        <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10">
            <div class="award-card group">
                <div class="award-number">10K</div>
                <h3 class="font-['Poppins']">Primeiro Marco</h3>
                <p class="font-['Poppins'] text-gray-400">Os primeiros 10.000 usuários que acreditaram na plataforma e fundaram nossa história.</p>
            </div>
            <div class="award-card group">
                <div class="award-number">100K</div>
                <h3 class="font-['Poppins']">Reconhecimento</h3>
                <p class="font-['Poppins'] text-gray-400">Mais de cem mil usuários ampliando seu impacto e consolidando nossa presença.</p>
            </div>
            <div class="award-card group">
                <div class="award-number">1M</div>
                <h3 class="font-['Poppins']">Um Milhão</h3>
                <p class="font-['Poppins'] text-gray-400">Um marco histórico de relevância global no mercado digital e tecnologia financeira.</p>
            </div>
        </div>
    </section>

</div>

@push('styles')
<style>
/* Smooth Scroll Behavior */
html, .scrollable-content { scroll-behavior: smooth; }

/* Carousel Styles */
.loop-images { perspective: 2000px; }
.carousel-track {
    --left: -200rem;
    min-width: calc(25rem * var(--total));
    height: 30rem;
    position: relative;
    transform-style: preserve-3d;
}
.carousel-item {
    position: absolute;
    width: 25rem;
    height: 30rem;
    left: 100%;
    display: flex;
    justify-content: center;
    perspective: 1000px;
    transform-style: preserve-3d;
    animation: scroll-left var(--time) linear infinite;
    animation-delay: calc(var(--time) / var(--total) * (var(--i) - 1) - var(--time));
    will-change: left;
}
.carousel-item img {
    width: 100%; height: 100%; object-fit: cover; border-radius: 20px;
    transform: rotateY(-35deg); transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
    mask: linear-gradient(black 85%, transparent 100%);
    border: 1px solid rgba(212, 175, 55, 0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
}
.carousel-item:hover img { transform: rotateY(0deg) translateY(-20px) scale(1.1); border-color: #D4AF37; box-shadow: 0 30px 60px rgba(212, 175, 55, 0.2); }
@keyframes scroll-left { to { left: var(--left); } }

/* Cards & Content */
.award-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 24px;
    padding: 40px;
    transition: all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-align: center;
    backdrop-filter: blur(15px);
}
.award-card:hover { border-color: #D4AF37; transform: translateY(-10px); background: rgba(212, 175, 55, 0.03); }
.award-number {
    font-size: 60px; font-weight: 900; background: linear-gradient(135deg,#8a6d1d,#D4AF37,#f5de8a);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    margin-bottom: 10px; font-family: 'Poppins', sans-serif;
}
.award-card h3 { color: white; margin-bottom: 12px; font-weight: 700; font-size: 22px; }
.award-card p { line-height: 1.6; font-size: 15px; }

/* Scrollbar */
.scrollable-content::-webkit-scrollbar { width: 5px; }
.scrollable-content::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 10px; }

/* Custom Animations */
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(4px); }
}
</style>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const scrollBtn = document.querySelector('.scroll-btn');
        const scrollContainer = document.querySelector('.scrollable-content');
        const target = document.getElementById('historia');

        if (scrollBtn && scrollContainer && target) {
            scrollBtn.addEventListener('click', (e) => {
                e.preventDefault();
                scrollContainer.scrollTo({
                    top: target.offsetTop - 20,
                    behavior: 'smooth'
                });
            });
        }
    });
</script>
@endpush
@endsection
