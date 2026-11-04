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

Learners build one online store — **Harbour & Co**, a Singapore fashion label making breathable city wear for the tropics — from an empty WordPress site, with ChatGPT drafting the work and the learner checking and approving it. AI designs the homepage, shoots and writes the products, builds the catalogue CSV, drafts the store policies, writes the SEO and the launch campaign; the learner checks every fact against the brief and decides what goes live. The day runs as one storyline, one lab per topic: set the store up with GenAI (Labs 1–4), then get it found in search, market the launch and read the results (Lab 5).

## Learning outcomes

1. Set up and monitor WooCommerce CMS to ensure adherence to guidelines and policies.
2. Edit and curate a product website on WooCommerce CMS.
3. Maintain product web content on WooCommerce CMS.
4. Recommend payment and shipping methods on WooCommerce CMS to improve customer experience.
5. Manage issues related to WooCommerce CMS such as sales, out-of-stock and promotions.
6. Manage WooCommerce CMS performance.

## Topics covered

1. **Overview of AI for eCommerce Store** — Generative AI tools for WooCommerce (ChatGPT, Gemini or Claude, model tiers, effective prompts, WordPress AI Connectors); setting up WooCommerce; themes, widgets and shortcodes; settings, currency, 9% GST and roles
2. **Manage Products with AI** — products and the Product Data panel, AI product photos and galleries, variable products, shipping classes, categories, tags and attributes, downloadable products, up-sells and cross-sells, the CSV importer, the Customizer and WooCommerce Blocks
3. **Manage Payments and Shipping with AI** — payment methods and gateways, shipping zones, flat rate, free shipping and local pickup, store policies drafted with AI
4. **Manage Sales with AI** — orders and issues, refunds and restocking, coupons and promotions, customer replies drafted with AI
5. **Manage Store Performance with AI** — sales reports, dashboard widgets, Google Analytics, SEO in the age of AI search (SEO, AEO, GEO, E-E-A-T), and AI marketing campaigns that measure themselves

## Labs

One lab per topic. Each lab folder holds an instructions PDF with a Prompt Playbook page, `COPY-PROMPTS.txt`, mock data, product assets and templates. All prompts are also on the offline [Lab Prompts page](labs/prompts/index.html).

| Lab | Title |
|---|---|
| 1 | [Launch Harbour & Co — WooCommerce Plus an AI-Designed Homepage](labs/lab-01-launch-harbour-and-co/) |
| 2 | [Shoot, Write and Import the New-Season Catalogue with AI](labs/lab-02-ai-product-catalogue/) |
| 3 | [AI Drafts the Policies, You Switch On Payments and Shipping](labs/lab-03-ai-policies-payments-shipping/) |
| 4 | [Run a Day of Orders — Refund, Restock, Reply with AI](labs/lab-04-orders-refunds-ai-replies/) |
| 5 | [Get Found, Then Launch the Resort Linen Drop — SEO, Social, Results](labs/lab-05-ai-seo-and-campaign/) |

## Courseware

| Artifact | Files |
|---|---|
| Slide deck (v2.1) | [PPTX](courseware/Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI-v2.1.pptx) · [PDF](courseware/Build%20a%20Professional%20eCommerce%20Store%20with%20Generative%20AI-v2.1.pdf) |
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
labs/         five lab folders and the offline Lab Prompts page
docker/       local WordPress + WooCommerce sandbox of the finished store
assets/       screenshots and images referenced by the Learner Guide
```

## Distribution

This repository holds the public learner courseware. Trainer-only material is kept off GitHub. Harbour & Co is a fictional brand and all store data is synthetic. The demo training sites listed in the labs are reset before every class and must not be used for a real shop.

---

Provided by [Tertiary Infotech Academy Pte Ltd](https://www.tertiarycourses.com.sg) · enquiry@tertiaryinfotech.com · +65 6100 0613
