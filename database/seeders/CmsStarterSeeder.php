<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Seeder;

class CmsStarterSeeder extends Seeder
{
    public function run(): void
    {
        $contact = Form::updateOrCreate(['key' => 'contact'], [
            'name' => ['en' => 'Contact us', 'ar' => 'تواصل معنا'],
            'description' => ['en' => 'Tell us about your goals.', 'ar' => 'حدثنا عن أهدافك.'],
            'submit_label' => ['en' => 'Send enquiry', 'ar' => 'إرسال الاستفسار'],
            'success_message' => ['en' => 'Thank you. We will be in touch soon.', 'ar' => 'شكرًا لك. سنتواصل معك قريبًا.'],
            'notify_emails' => ['hello@digify.test'], 'stores_submissions' => true, 'retention_days' => 730, 'is_active' => true,
        ]);

        $this->syncFields($contact, [
            ['key' => 'name', 'type' => 'text', 'label' => ['en' => 'Name', 'ar' => 'الاسم'], 'rules' => ['required', 'string', 'max:120'], 'width' => 'half'],
            ['key' => 'email', 'type' => 'email', 'label' => ['en' => 'Email', 'ar' => 'البريد الإلكتروني'], 'rules' => ['required', 'email', 'max:255'], 'width' => 'half'],
            ['key' => 'company', 'type' => 'text', 'label' => ['en' => 'Company', 'ar' => 'الشركة'], 'rules' => ['nullable', 'string', 'max:160'], 'width' => 'full'],
            ['key' => 'message', 'type' => 'textarea', 'label' => ['en' => 'How can we help?', 'ar' => 'كيف يمكننا مساعدتك؟'], 'rules' => ['required', 'string', 'max:5000'], 'width' => 'full'],
        ]);

        $openApplication = Form::updateOrCreate(['key' => 'open-application'], [
            'name' => ['en' => 'Open application', 'ar' => 'طلب توظيف مفتوح'],
            'description' => ['en' => 'No matching vacancy? Introduce yourself to our team.', 'ar' => 'لم تجد الوظيفة المناسبة؟ عرّفنا بنفسك.'],
            'submit_label' => ['en' => 'Submit application', 'ar' => 'إرسال الطلب'],
            'success_message' => ['en' => 'Your application has been received.', 'ar' => 'تم استلام طلبك.'],
            'notify_emails' => ['careers@digify.test'], 'stores_submissions' => true, 'retention_days' => 730, 'is_active' => true,
        ]);

        $this->syncFields($openApplication, [
            ['key' => 'full_name', 'type' => 'text', 'label' => ['en' => 'Full name', 'ar' => 'الاسم الكامل'], 'rules' => ['required', 'string', 'max:160'], 'width' => 'half'],
            ['key' => 'email', 'type' => 'email', 'label' => ['en' => 'Email', 'ar' => 'البريد الإلكتروني'], 'rules' => ['required', 'email', 'max:255'], 'width' => 'half'],
            ['key' => 'phone', 'type' => 'tel', 'label' => ['en' => 'Phone', 'ar' => 'رقم الهاتف'], 'rules' => ['required', 'string', 'max:40'], 'width' => 'half'],
            ['key' => 'portfolio_url', 'type' => 'text', 'label' => ['en' => 'Portfolio URL', 'ar' => 'رابط معرض الأعمال'], 'rules' => ['nullable', 'url', 'max:500'], 'width' => 'half'],
            ['key' => 'introduction', 'type' => 'textarea', 'label' => ['en' => 'Tell us about yourself', 'ar' => 'حدثنا عن نفسك'], 'rules' => ['required', 'string', 'max:5000'], 'width' => 'full'],
            ['key' => 'cv', 'type' => 'file', 'label' => ['en' => 'CV', 'ar' => 'السيرة الذاتية'], 'rules' => ['required', 'mimes:pdf,docx'], 'width' => 'full'],
        ]);

        Page::updateOrCreate(['slug->en' => 'home'], [
            'slug' => ['en' => 'home', 'ar' => 'الرئيسية'], 'title' => ['en' => 'Digify', 'ar' => 'ديجيفاي'],
            'excerpt' => ['en' => 'Digital experiences with impact.', 'ar' => 'تجارب رقمية تصنع الأثر.'],
            'blocks' => [
                ['type' => 'hero_cinematic', 'data' => [
                    'eyebrow' => ['en' => 'Digify Agency', 'ar' => 'وكالة ديجيفاي'],
                    'title' => ['en' => 'Digital experiences with impact', 'ar' => 'تجارب رقمية تصنع الأثر'],
                    'body' => ['en' => 'Strategy, design, and technology working as one.', 'ar' => 'الاستراتيجية والتصميم والتقنية تعمل معًا.'],
                    'cta_label' => ['en' => 'Start a conversation', 'ar' => 'ابدأ محادثة'], 'cta_url' => '#contact', 'dark_overlay' => true,
                ]],
                ['type' => 'form', 'data' => ['title' => ['en' => 'Let’s build what matters', 'ar' => 'لنبنِ ما يصنع الفرق'], 'form_id' => $contact->id]],
            ],
            'seo' => ['en' => ['title' => 'Digify — Digital experiences with impact', 'description' => 'Strategy, design, and technology for ambitious organizations.'], 'ar' => ['title' => 'ديجيفاي — تجارب رقمية تصنع الأثر', 'description' => 'استراتيجية وتصميم وتقنية للمؤسسات الطموحة.']],
            'template' => 'landing', 'status' => 'published', 'published_at' => now(), 'is_homepage' => true,
        ]);

        $menu = Menu::updateOrCreate(['key' => 'main'], ['name' => ['en' => 'Main navigation', 'ar' => 'القائمة الرئيسية']]);
        $menu->allItems()->updateOrCreate(['url' => '/careers/'], ['label' => ['en' => 'Careers', 'ar' => 'الوظائف'], 'target' => 'same', 'sort_order' => 10]);
        $menu->allItems()->updateOrCreate(['url' => '/careers/open-application/'], ['label' => ['en' => 'Open application', 'ar' => 'طلب توظيف مفتوح'], 'target' => 'same', 'sort_order' => 20]);
    }

    private function syncFields(Form $form, array $fields): void
    {
        foreach ($fields as $index => $field) {
            $form->fields()->updateOrCreate(['key' => $field['key']], [
                ...$field, 'placeholder' => null, 'help_text' => null, 'options' => null, 'conditional_logic' => null, 'sort_order' => $index,
            ]);
        }
    }
}
