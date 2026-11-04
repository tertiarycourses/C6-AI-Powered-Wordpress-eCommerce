# Build a Professional eCommerce Store with Generative AI

A one-day, hands-on course on setting up, running and marketing a WooCommerce store with Generative AI as your co-worker.

| Course detail | Information |
|---|---|
| Course code | `C6` |
| Programme | Non-WSQ |
| Duration | 1 day · 9:30am – 5:30pm · 7.5 instructional hours |
| Delivery | Instructor-led classroom with hands-on labs on a live WordPress + WooCommerce store |
| Registration | [View course details and register](https://www.tertiarycourses.com.sg/build-a-professional-ecommerce-store-with-generative-ai.html) |

![Course cover](screenshot.png)

## About the course

Learners build one online store — **Harbour & Co**, a Singapore fashion label making breathable city wear for the tropics — from an empty WordPress site, with ChatGPT drafting the work and the learner checking and approving it. AI designs the homepage, shoots and writes the products, builds the catalogue CSV, drafts the store policies, writes the SEO and the launch campaign; the learner checks every fact against the brief and decides what goes live. The day runs as one storyline: set the store up with GenAI (Labs 1–5), get it found in search (Lab 6), then market the launch and read the results (Lab 7).

## Learning outcomes

1. Set up and monitor WooCommerce CMS to ensure adherence to guidelines and policies.
2. Edit and curate a product website on WooCommerce CMS.
3. Maintain product web content on WooCommerce CMS.
4. Recommend payment and shipping methods on WooCommerce CMS to improve customer experience.
5. Manage issues related to WooCommerce CMS such as sales, out-of-stock and promotions.
6. Manage WooCommerce CMS performance.

## Topics covered

1. AI for WordPress and WooCommerce — choosing ChatGPT, Gemini or Claude, model tiers, effective prompts, WordPress AI Connectors
2. Overview of WooCommerce CMS — installing WooCommerce, the setup wizard, themes, widgets, settings, currency and tax
3. Manage Products on WooCommerce Store — product types, AI product photos and copy, variations, categories and attributes, CSV import
4. Manage Payments and Shipping — store policies, payment methods and gateways, shipping zones, flat rate, free shipping and local pickup
5. Manage Sales on WooCommerce Store — orders and statuses, refunds and restocking, coupons and promotions
6. Manage WooCommerce Performance — reports and analytics, eCommerce metrics, Google Analytics
7. SEO in the Age of AI Search — SEO, AEO and GEO, E-E-A-T, AI for SEO, on-page SEO, digital marketing with GenAI

## Labs

Each lab folder holds an instructions PDF with a Prompt Playbook page, `COPY-PROMPTS.txt`, mock data, product assets and templates. All prompts are also on the offline [Lab Prompts page](labs/prompts/index.html).

| Lab | Title |
|---|---|
| 1 | [Launch Harbour & Co — WooCommerce Plus an AI-Designed Homepage](labs/lab-01-launch-harbour-and-co/) |
| 2 | [Shoot and Write a Product with AI](labs/lab-02-ai-product-photos-and-copy/) |
| 3 | [Let AI Build the New-Season Catalogue](labs/lab-03-ai-builds-the-catalogue/) |
| 4 | [AI Drafts the Policies, You Switch On Payments and Shipping](labs/lab-04-ai-policies-payments-shipping/) |
| 5 | [Run a Day of Orders — Refund, Restock, Reply with AI](labs/lab-05-orders-refunds-ai-replies/) |
| 6 | [Get Found — AI Writes the SEO, You Approve It](labs/lab-06-ai-seo/) |
| 7 | [Launch the Resort Linen Drop — Social, Newsletter, Results](labs/lab-07-ai-digital-marketing/) |

## Courseware

| Artifact | Files |
|---|---|
| Slide deck (v2.0) | [PPTX](courseware/Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI-v2.0.pptx) · [PDF](courseware/Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI-v2.0.pdf) |
| Lesson Plan | [DOCX](courseware/LP-Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI.docx) · [PDF](courseware/LP-Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI.pdf) |
| Learner Guide | [DOCX](courseware/LG-Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI.docx) · [PDF](courseware/LG-Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI.pdf) · [Markdown](LG-Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI.md) |

## Rebuild the store at home

The [`docker/`](docker/) folder builds the finished Harbour & Co store locally with the same plugins and data used in class:

```bash
cd docker
docker compose up -d
bash setup.sh
# then open http://localhost:8088  (admin: /wp-admin)
```

## Repository structure

```text
courseware/   slide deck, Lesson Plan and Learner Guide (editable + PDF)
labs/         seven lab folders and the offline Lab Prompts page
docker/       local WordPress + WooCommerce sandbox of the finished store
assets/       screenshots and images referenced by the Learner Guide
```

## Distribution

This repository holds the public learner courseware. Trainer-only material is kept off GitHub. Harbour & Co is a fictional brand and all store data is synthetic. The demo training sites listed in the labs are reset before every class and must not be used for a real shop.

---

Provided by [Tertiary Infotech Academy Pte Ltd](https://www.tertiarycourses.com.sg) · enquiry@tertiaryinfotech.com · +65 6100 0613
