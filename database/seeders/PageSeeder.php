<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates sample pages:
     * - Blog posts with lorem ipsum content
     * - FAQ page with common questions
     * - Standard pages
     * 
     * Usage: php artisan db:seed --class=PageSeeder
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding pages...');
        
        // Create blog posts
        $this->createBlogPosts();
        
        // Create FAQ page
        $this->createFaqPage();
        
        // Create standard pages
        $this->createStandardPages();
        
        $this->command->info('✅ Pages seeded successfully!');
    }
    
    /**
     * Create sample blog posts
     */
    private function createBlogPosts(): void
    {
        $blogPosts = [
            [
                'title' => [
                    'en' => 'Welcome to Our Platform',
                    'cs' => 'Vítejte na naší platformě',
                ],
                'slug' => 'welcome-to-our-platform',
                'description' => [
                    'en' => 'Discover our new platform and all the amazing features we have to offer. Learn how to get started and make the most of your experience.',
                    'cs' => 'Objevte naši novou platformu a všechny úžasné funkce, které nabízíme. Zjistěte, jak začít a jak maximálně využít své zkušenosti.',
                ],
                'content' => [
                    'en' => $this->generateBlogContent('en'),
                    'cs' => $this->generateBlogContent('cs'),
                ],
            ],
            [
                'title' => [
                    'en' => 'How to Create the Perfect Profile',
                    'cs' => 'Jak vytvořit dokonalý profil',
                ],
                'slug' => 'how-to-create-perfect-profile',
                'description' => [
                    'en' => 'A comprehensive guide to creating an attractive and professional profile that stands out from the crowd.',
                    'cs' => 'Komplexní průvodce vytvořením atraktivního a profesionálního profilu, který vynikne z davu.',
                ],
                'content' => [
                    'en' => $this->generateProfileGuideContent('en'),
                    'cs' => $this->generateProfileGuideContent('cs'),
                ],
            ],
            [
                'title' => [
                    'en' => 'Safety Tips for Our Community',
                    'cs' => 'Bezpečnostní tipy pro naši komunitu',
                ],
                'slug' => 'safety-tips',
                'description' => [
                    'en' => 'Important safety guidelines and tips to ensure a secure and positive experience for everyone.',
                    'cs' => 'Důležité bezpečnostní pokyny a tipy pro zajištění bezpečné a pozitivní zkušenosti pro všechny.',
                ],
                'content' => [
                    'en' => $this->generateSafetyContent('en'),
                    'cs' => $this->generateSafetyContent('cs'),
                ],
            ],
            [
                'title' => [
                    'en' => 'New Features Announcement',
                    'cs' => 'Oznámení nových funkcí',
                ],
                'slug' => 'new-features-announcement',
                'description' => [
                    'en' => 'Exciting new features have been added to our platform. Check out what\'s new and how it can benefit you.',
                    'cs' => 'Na naši platformu byly přidány vzrušující nové funkce. Podívejte se, co je nového a jak vám to může pomoci.',
                ],
                'content' => [
                    'en' => $this->generateFeaturesContent('en'),
                    'cs' => $this->generateFeaturesContent('cs'),
                ],
            ],
            [
                'title' => [
                    'en' => 'Understanding Our Verification Process',
                    'cs' => 'Pochopení našeho procesu ověření',
                ],
                'slug' => 'verification-process',
                'description' => [
                    'en' => 'Learn about our verification process and why it\'s important for building trust in our community.',
                    'cs' => 'Zjistěte více o našem procesu ověření a proč je důležitý pro budování důvěry v naší komunitě.',
                ],
                'content' => [
                    'en' => $this->generateVerificationContent('en'),
                    'cs' => $this->generateVerificationContent('cs'),
                ],
            ],
        ];
        
        foreach ($blogPosts as $post) {
            $page = Page::create([
                'title' => $post['title'],
                'slug' => $post['slug'],
                'type' => 'blog',
                'description' => $post['description'],
                'content' => $post['content'],
                'display_in_menu' => false,
                'display_in_footer' => false,
                'is_published' => true,
            ]);
            
            // Add header image from picsum
            try {
                $randomSeed = 'blog-' . $post['slug'] . '-' . uniqid();
                $imageUrl = "https://picsum.photos/seed/{$randomSeed}/1200/400";
                
                // Try to download with retry logic
                $maxRetries = 3;
                $retry = 0;
                $success = false;
                
                while ($retry < $maxRetries && !$success) {
                    try {
                        $page->addMediaFromUrl($imageUrl)
                            ->toMediaCollection('header-image');
                        $success = true;
                    } catch (\Exception $retryException) {
                        $retry++;
                        if ($retry < $maxRetries) {
                            usleep(500000); // Wait 0.5s before retry
                        }
                    }
                }
                
                if (!$success) {
                    $this->command->warn("  ⚠️  Could not download header image for: {$post['slug']}");
                }
            } catch (\Exception $e) {
                $this->command->warn("  ⚠️  Failed to add header image: {$e->getMessage()}");
            }
        }
        
        $this->command->info('  ✓ Created ' . count($blogPosts) . ' blog posts');
    }
    
    /**
     * Create FAQ page
     */
    private function createFaqPage(): void
    {
        Page::create([
            'title' => [
                'en' => 'FAQ',
                'cs' => 'FAQ',
            ],
            'slug' => 'faq',
            'type' => 'page',
            'description' => [
                'en' => 'Find answers to the most common questions about our platform.',
                'cs' => 'Najděte odpovědi na nejčastější otázky o naší platformě.',
            ],
            'content' => [
                'en' => $this->generateFaqContent('en'),
                'cs' => $this->generateFaqContent('cs'),
            ],
            'display_in_menu' => true,
            'display_in_footer' => true,
            'is_published' => true,
        ]);
        
        $this->command->info('  ✓ Created FAQ page');
    }
    
    /**
     * Create standard pages
     */
    private function createStandardPages(): void
    {
        $pages = [
            [
                'title' => [
                    'en' => 'VIP & Premium',
                    'cs' => 'VIP & Premium',
                ],
                'slug' => 'vip-premium',
                'content' => [
                    'en' => $this->generateAboutContent('en'),
                    'cs' => $this->generateAboutContent('cs'),
                ],
                'display_in_menu' => true,
                'display_in_footer' => true,
            ],
            [
                'title' => [
                    'en' => 'Ethics and Safety',
                    'cs' => 'Etika a bezpečnost',
                ],
                'slug' => 'ethics',
                'content' => [
                    'en' => $this->generateSafetyContent('en'),
                    'cs' => $this->generateSafetyContent('cs'),
                ],
                'display_in_menu' => true,
                'display_in_footer' => true,
            ],
            [
                'title' => [
                    'en' => 'Contact Us',
                    'cs' => 'Kontakt',
                ],
                'slug' => 'contact',
                'content' => [
                    'en' => $this->generateContactContent('en'),
                    'cs' => $this->generateContactContent('cs'),
                ],
                'display_in_menu' => true,
                'display_in_footer' => true,
            ],
            [
                'title' => [
                    'en' => 'Privacy Policy',
                    'cs' => 'Ochrana osobních údajů',
                ],
                'slug' => 'privacy-policy',
                'content' => [
                    'en' => $this->generatePrivacyContent('en'),
                    'cs' => $this->generatePrivacyContent('cs'),
                ],
                'display_in_menu' => false,
                'display_in_footer' => true,
            ],
            [
                'title' => [
                    'en' => 'Terms of Service',
                    'cs' => 'Obchodní podmínky',
                ],
                'slug' => 'terms-of-service',
                'content' => [
                    'en' => $this->generateTermsContent('en'),
                    'cs' => $this->generateTermsContent('cs'),
                ],
                'display_in_menu' => false,
                'display_in_footer' => true,
            ],
        ];
        
        foreach ($pages as $pageData) {
            Page::create([
                'title' => $pageData['title'],
                'slug' => $pageData['slug'],
                'type' => 'page',
                'description' => null,
                'content' => $pageData['content'],
                'display_in_menu' => $pageData['display_in_menu'],
                'display_in_footer' => $pageData['display_in_footer'],
                'is_published' => true,
            ]);
        }
        
        $this->command->info('  ✓ Created ' . count($pages) . ' standard pages');
    }
    
    /**
     * Generate FAQ content with blocks
     */
    private function generateFaqContent(string $locale): array
    {
        $faqs = $locale === 'cs' ? [
            [
                'question' => 'Jak se mohu zaregistrovat?',
                'answer' => 'Registrace je snadná! Klikněte na tlačítko "Registrace" v pravém horním rohu stránky, vyplňte požadované údaje a potvrďte svou e-mailovou adresu. Celý proces zabere pouze pár minut.',
            ],
            [
                'question' => 'Je používání platformy zdarma?',
                'answer' => 'Základní funkce naší platformy jsou zcela zdarma. Nabízíme také prémiové funkce pro uživatele, kteří chtějí rozšířit své možnosti a získat větší viditelnost.',
            ],
            [
                'question' => 'Jak mohu upravit svůj profil?',
                'answer' => 'Po přihlášení klikněte na svůj avatar v pravém horním rohu a vyberte "Můj profil". Zde můžete upravit všechny své informace, nahrát fotografie a aktualizovat své služby.',
            ],
            [
                'question' => 'Jak funguje proces ověření?',
                'answer' => 'Ověření profilu je dobrovolné, ale doporučené. Pro ověření potřebujeme fotografii vašeho dokladu totožnosti a selfie fotografii. Všechna data zpracováváme v souladu s GDPR.',
            ],
            [
                'question' => 'Jak mohu kontaktovat podporu?',
                'answer' => 'Náš tým podpory je k dispozici 24/7. Můžete nás kontaktovat prostřednictvím kontaktního formuláře, e-mailu nebo telefonicky. Obvykle odpovídáme do 24 hodin.',
            ],
            [
                'question' => 'Mohu smazat svůj účet?',
                'answer' => 'Ano, svůj účet můžete kdykoli smazat v nastavení profilu. Po smazání budou všechna vaše data trvale odstraněna do 30 dnů.',
            ],
            [
                'question' => 'Jak jsou chráněny moje osobní údaje?',
                'answer' => 'Bezpečnost vašich dat je naší prioritou. Používáme šifrování SSL, bezpečné servery a pravidelně aktualizujeme naše bezpečnostní protokoly. Více informací najdete v našich Zásadách ochrany osobních údajů.',
            ],
            [
                'question' => 'Jak mohu nahlásit nevhodné chování?',
                'answer' => 'Pokud narazíte na nevhodné chování, použijte tlačítko "Nahlásit" na profilu uživatele nebo nás kontaktujte přímo. Každé hlášení pečlivě prověřujeme.',
            ],
        ] : [
            [
                'question' => 'How do I register?',
                'answer' => 'Registration is easy! Click the "Register" button in the top right corner of the page, fill in the required information, and confirm your email address. The whole process takes just a few minutes.',
            ],
            [
                'question' => 'Is the platform free to use?',
                'answer' => 'Basic features of our platform are completely free. We also offer premium features for users who want to expand their options and gain more visibility.',
            ],
            [
                'question' => 'How can I edit my profile?',
                'answer' => 'After logging in, click on your avatar in the top right corner and select "My Profile". Here you can edit all your information, upload photos, and update your services.',
            ],
            [
                'question' => 'How does the verification process work?',
                'answer' => 'Profile verification is voluntary but recommended. For verification, we need a photo of your ID and a selfie photo. All data is processed in accordance with GDPR.',
            ],
            [
                'question' => 'How can I contact support?',
                'answer' => 'Our support team is available 24/7. You can contact us through the contact form, email, or phone. We usually respond within 24 hours.',
            ],
            [
                'question' => 'Can I delete my account?',
                'answer' => 'Yes, you can delete your account at any time in your profile settings. After deletion, all your data will be permanently removed within 30 days.',
            ],
            [
                'question' => 'How is my personal data protected?',
                'answer' => 'The security of your data is our priority. We use SSL encryption, secure servers, and regularly update our security protocols. More information can be found in our Privacy Policy.',
            ],
            [
                'question' => 'How can I report inappropriate behavior?',
                'answer' => 'If you encounter inappropriate behavior, use the "Report" button on the user\'s profile or contact us directly. We carefully review every report.',
            ],
        ];
        
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Často kladené otázky' : 'Frequently Asked Questions',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs' 
                        ? 'Zde najdete odpovědi na nejčastější otázky o naší platformě. Pokud nenajdete, co hledáte, neváhejte nás kontaktovat.'
                        : 'Here you will find answers to the most common questions about our platform. If you can\'t find what you\'re looking for, don\'t hesitate to contact us.',
                ],
            ],
            [
                'type' => 'App\\Filament\\Blocks\\Faq',
                'data' => [
                    'heading' => $locale === 'cs' ? 'Obecné otázky' : 'General Questions',
                    'items' => array_slice($faqs, 0, 4),
                ],
            ],
            [
                'type' => 'App\\Filament\\Blocks\\Faq',
                'data' => [
                    'heading' => $locale === 'cs' ? 'Bezpečnost a soukromí' : 'Security & Privacy',
                    'items' => array_slice($faqs, 4),
                ],
            ],
        ];
    }
    
    /**
     * Generate blog content
     */
    private function generateBlogContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Vítejte na naší platformě' : 'Welcome to Our Platform',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Jsme nadšeni, že vás můžeme přivítat na naší platformě. Vytvořili jsme prostor, kde se můžete prezentovat profesionálně a bezpečně. Naším cílem je poskytnout vám nejlepší možnou zkušenost.'
                        : 'We are excited to welcome you to our platform. We have created a space where you can present yourself professionally and safely. Our goal is to provide you with the best possible experience.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Co nabízíme' : 'What We Offer',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Naše platforma nabízí širokou škálu funkcí navržených tak, aby vám pomohly uspět. Od snadného vytvoření profilu po pokročilé nástroje pro správu vašeho času a služeb – máme vše, co potřebujete.'
                        : 'Our platform offers a wide range of features designed to help you succeed. From easy profile creation to advanced tools for managing your time and services – we have everything you need.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.'
                        : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                ],
            ],
        ];
    }
    
    /**
     * Generate profile guide content
     */
    private function generateProfileGuideContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Jak vytvořit dokonalý profil' : 'How to Create the Perfect Profile',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Váš profil je vaší vizitkou. Je to první věc, kterou potenciální klienti uvidí, a proto je důležité udělat dobrý první dojem. Zde je několik tipů, jak vytvořit profil, který vynikne.'
                        : 'Your profile is your business card. It\'s the first thing potential clients will see, which is why it\'s important to make a good first impression. Here are some tips on how to create a profile that stands out.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Kvalitní fotografie' : 'Quality Photos',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Investujte do kvalitních fotografií. Dobré osvětlení, čisté pozadí a profesionální vzhled mohou udělat zázraky. Doporučujeme nahrát alespoň 3-5 různých fotografií.'
                        : 'Invest in quality photos. Good lighting, clean background, and professional appearance can work wonders. We recommend uploading at least 3-5 different photos.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Popis profilu' : 'Profile Description',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Napište zajímavý a upřímný popis sebe sama. Zmíňte své silné stránky, zájmy a to, co vás odlišuje od ostatních. Buďte autentičtí – klienti to ocení.'
                        : 'Write an interesting and honest description of yourself. Mention your strengths, interests, and what sets you apart from others. Be authentic – clients will appreciate it.',
                ],
            ],
        ];
    }
    
    /**
     * Generate safety content
     */
    private function generateSafetyContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Bezpečnostní tipy' : 'Safety Tips',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Vaše bezpečnost je naší nejvyšší prioritou. Zde jsou důležité tipy, které vám pomohou zůstat v bezpečí při používání naší platformy.'
                        : 'Your safety is our highest priority. Here are important tips to help you stay safe while using our platform.',
                ],
            ],
            [
                'type' => 'App\\Filament\\Blocks\\Faq',
                'data' => [
                    'heading' => $locale === 'cs' ? 'Základní bezpečnostní pravidla' : 'Basic Safety Rules',
                    'items' => $locale === 'cs' ? [
                        ['question' => 'Nikdy nesdílejte osobní údaje', 'answer' => 'Nesdílejte své telefonní číslo, adresu nebo finanční údaje před ověřením klienta.'],
                        ['question' => 'Vždy informujte někoho blízkého', 'answer' => 'Před každou schůzkou dejte vědět příteli nebo rodinnému příslušníkovi, kam jdete.'],
                        ['question' => 'Důvěřujte svým instinktům', 'answer' => 'Pokud se něco nezdá v pořádku, neváhejte odmítnout nebo odejít.'],
                    ] : [
                        ['question' => 'Never share personal information', 'answer' => 'Don\'t share your phone number, address, or financial details before verifying the client.'],
                        ['question' => 'Always inform someone close', 'answer' => 'Before each meeting, let a friend or family member know where you\'re going.'],
                        ['question' => 'Trust your instincts', 'answer' => 'If something doesn\'t feel right, don\'t hesitate to refuse or leave.'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Generate features content
     */
    private function generateFeaturesContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Nové funkce' : 'New Features',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Rádi vám představujeme nové funkce, které jsme přidali na základě vašich požadavků a zpětné vazby.'
                        : 'We\'re happy to introduce new features that we\'ve added based on your requests and feedback.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Card',
                'data' => [
                    'content' => [
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                            'data' => [
                                'content' => $locale === 'cs' ? 'Vylepšené vyhledávání' : 'Improved Search',
                                'level' => 'h3',
                            ],
                        ],
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                            'data' => [
                                'content' => $locale === 'cs'
                                    ? 'Nový vyhledávací systém s pokročilými filtry vám umožní najít přesně to, co hledáte.'
                                    : 'New search system with advanced filters allows you to find exactly what you\'re looking for.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Card',
                'data' => [
                    'content' => [
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                            'data' => [
                                'content' => $locale === 'cs' ? 'Rychlejší načítání' : 'Faster Loading',
                                'level' => 'h3',
                            ],
                        ],
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                            'data' => [
                                'content' => $locale === 'cs'
                                    ? 'Optimalizovali jsme výkon celé platformy pro rychlejší a plynulejší zážitek.'
                                    : 'We\'ve optimized the performance of the entire platform for a faster and smoother experience.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Generate verification content
     */
    private function generateVerificationContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Proces ověření' : 'Verification Process',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Ověření profilu je důležitým krokem k budování důvěry v naší komunitě. Ověřené profily získávají více pozornosti a důvěry od klientů.'
                        : 'Profile verification is an important step towards building trust in our community. Verified profiles gain more attention and trust from clients.',
                ],
            ],
            [
                'type' => 'App\\Filament\\Blocks\\Faq',
                'data' => [
                    'heading' => $locale === 'cs' ? 'Kroky ověření' : 'Verification Steps',
                    'items' => $locale === 'cs' ? [
                        ['question' => 'Krok 1: Nahrání dokladu', 'answer' => 'Nahrajte fotografii svého platného dokladu totožnosti (občanský průkaz, pas).'],
                        ['question' => 'Krok 2: Selfie fotografie', 'answer' => 'Pořiďte selfie fotografii, která odpovídá fotografii na dokladu.'],
                        ['question' => 'Krok 3: Kontrola', 'answer' => 'Náš tým zkontroluje vaše dokumenty, obvykle do 24-48 hodin.'],
                    ] : [
                        ['question' => 'Step 1: Document Upload', 'answer' => 'Upload a photo of your valid identification document (ID card, passport).'],
                        ['question' => 'Step 2: Selfie Photo', 'answer' => 'Take a selfie photo that matches the photo on your document.'],
                        ['question' => 'Step 3: Review', 'answer' => 'Our team will review your documents, usually within 24-48 hours.'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Generate about page content
     */
    private function generateAboutContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'O nás' : 'About Us',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Jsme tým nadšenců, kteří věří v sílu technologií ke zlepšení života lidí. Naše platforma byla vytvořena s jedním cílem – poskytnout bezpečné a profesionální prostředí pro naši komunitu.'
                        : 'We are a team of enthusiasts who believe in the power of technology to improve people\'s lives. Our platform was created with one goal – to provide a safe and professional environment for our community.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Naše mise' : 'Our Mission',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.'
                        : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.',
                ],
            ],
        ];
    }
    
    /**
     * Generate terms content
     */
    private function generateTermsContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Obchodní podmínky' : 'Terms of Service',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Tyto obchodní podmínky upravují pravidla používání naší platformy. Používáním našich služeb souhlasíte s těmito podmínkami.'
                        : 'These Terms of Service govern the rules for using our platform. By using our services, you agree to these terms.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? '1. Obecná ustanovení' : '1. General Provisions',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? '2. Registrace a účet' : '2. Registration and Account',
                    'level' => 'h2',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                ],
            ],
        ];
    }
    
    /**
     * Generate privacy content
     */
    private function generatePrivacyContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Zásady ochrany osobních údajů' : 'Privacy Policy',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Ochrana vašich osobních údajů je pro nás velmi důležitá. Tyto zásady vysvětlují, jak shromažďujeme, používáme a chráníme vaše údaje.'
                        : 'The protection of your personal data is very important to us. This policy explains how we collect, use, and protect your data.',
                ],
            ],
            [
                'type' => 'App\\Filament\\Blocks\\Faq',
                'data' => [
                    'heading' => $locale === 'cs' ? 'Klíčové informace' : 'Key Information',
                    'items' => $locale === 'cs' ? [
                        ['question' => 'Jaké údaje shromažďujeme?', 'answer' => 'Shromažďujeme pouze údaje nezbytné pro poskytování našich služeb: jméno, e-mail, telefon a údaje profilu.'],
                        ['question' => 'Jak údaje používáme?', 'answer' => 'Údaje používáme k poskytování služeb, zlepšování platformy a komunikaci s vámi.'],
                        ['question' => 'Jak údaje chráníme?', 'answer' => 'Používáme šifrování, bezpečné servery a pravidelné bezpečnostní audity.'],
                    ] : [
                        ['question' => 'What data do we collect?', 'answer' => 'We only collect data necessary for providing our services: name, email, phone, and profile data.'],
                        ['question' => 'How do we use the data?', 'answer' => 'We use the data to provide services, improve the platform, and communicate with you.'],
                        ['question' => 'How do we protect the data?', 'answer' => 'We use encryption, secure servers, and regular security audits.'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Generate contact page content
     */
    private function generateContactContent(string $locale): array
    {
        return [
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                'data' => [
                    'content' => $locale === 'cs' ? 'Kontaktujte nás' : 'Contact Us',
                    'level' => 'h1',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Máte otázky nebo potřebujete pomoc? Neváhejte nás kontaktovat. Jsme tu pro vás!'
                        : 'Have questions or need help? Don\'t hesitate to contact us. We\'re here for you!',
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Card',
                'data' => [
                    'content' => [
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                            'data' => [
                                'content' => 'Email',
                                'level' => 'h3',
                            ],
                        ],
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                            'data' => [
                                'content' => 'info@example.com',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Card',
                'data' => [
                    'content' => [
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Heading',
                            'data' => [
                                'content' => $locale === 'cs' ? 'Telefon' : 'Phone',
                                'level' => 'h3',
                            ],
                        ],
                        [
                            'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                            'data' => [
                                'content' => '+420 123 456 789',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'SkyRaptor\\FilamentBlocksBuilder\\Blocks\\Typography\\Paragraph',
                'data' => [
                    'content' => $locale === 'cs'
                        ? 'Pracovní doba podpory: Pondělí - Pátek, 9:00 - 18:00'
                        : 'Support hours: Monday - Friday, 9:00 AM - 6:00 PM',
                ],
            ],
        ];
    }
}
