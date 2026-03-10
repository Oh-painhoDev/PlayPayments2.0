# 📘 Guia de Marca & Identidade Visual: $playpayments

## 1. Visão Geral da Marca
O **$playpayments** é um gateway de pagamento especializado em soluções PIX, focado em agilidade, segurança e uma experiência de usuário (UX) premium de última geração.

### Nome da Marca
- **Formatado**: `$playpayments` (sempre minúsculo iniciada com $)
- **Slogan Sugerido**: *Agilidade no Pagamento, Play no seu Negócio.*

---

## 2. Paleta de Cores
A marca utiliza um sistema de cores focado em Dark Mode com acentos vibrantes.

### Cores Principais
| Cor | HEX | Aplicação |
| :--- | :--- | :--- |
| **Deep Dark** | `#000000` | Fundo principal e containers de alto contraste. |
| **Interface Black** | `#0d0d0d` | Fundo secundário e áreas de dashboard. |
| **Emerald (Primary)** | `#10b981` | Botões de ação, status de sucesso e acentos. |
| **Cyan (Accent)** | `#21b3dd` | Detalhes de logo, gradientes e estados de foco. |
| **Gray Text** | `#f4f4f5` | Texto principal e títulos. |
| **Muted Text** | `#a1a1aa` | Legendas e informações secundárias. |

---

## 3. Tipografia
- **Fonte Principal**: `Inter` (Google Fonts)
- **Pesos Utilizados**: 400 (Regular), 500 (Medium), 600 (Semi-Bold), 700 (Bold)
- **Estilo**: Sans-serif, moderna e altamente legível em sistemas digitais.

---

## 4. Iconografia do Sistema
Utilizamos a biblioteca **Lucide/HeroIcons** em formato **SVG** para garantir a nitidez em qualquer tela e resolução.

### Padrão de Ícones (Dock)
Os ícones do Dock seguem um padrão minimalista com traço branco de `2px` em formato vetorial embutido (Data URI). Isso garante:
- **Zero delay** no carregamento.
- **Nitidez total** em telas Retina/4K.
- **Consistência visual** em todo o sistema.

---

## 5. Componentes de UI Assinatura
O **$playpayments** se diferencia pelo uso de componentes premium:

### macOS-Style Dock
- **Efeito**: Glassmorphism (`backdrop-blur-md`)
- **Fundo**: Transparente com blur dinâmico e bordas de 15% de opacidade branca.
- **Interação**: Magnificação orgânica com interpolação linear (Lerp) e animações de salto (Bounce) no clique.
- **Propósito**: Acesso rápido às ferramentas essenciais do dashboard sem poluir a área de conteúdo central.

---

## 6. Tom de Voz
- **Profissional porem Moderno**: A marca deve passar segurança mas não deve parecer "antiga" ou burocrática.
- **Tecnológico**: Uso de termos como "Gateway", "Seamless", "Flow", "Instantâneo".
- **Direto**: Comunicação clara, sem enrolação, facilitando a vida do lojista.

---

## 7. Diretórios de Ativos
- **Logos**: `public/images/playpayments-logo-top.webp` / `.svg`
- **Favicon**: `favicon.svg`
- **Componentes Blade**: `resources/views/components/MacosDock.blade.php`

---
*Atualizado em: 10 de Março de 2026*
*Responsável: Equipe de Design PlayPayments*
