# Project Brief: AI Chatbot Configuration Module

## 1. Overview
**Goal:** Develop a comprehensive "AI Configuration" dashboard for the Narapati Studio web application. This module allows admins to manage knowledge sources (websites, docs), configure live agent handovers, and test the bot.

**Reference:** The UI/UX should replicate the functionality and layout observed in the provided "Kata CX" screenshots.

## 2. Core Navigation Structure
The module consists of a main navigation bar with the following tabs:
1.  **Playground** (Chat interface for testing)
2.  **Sources** (Knowledge base management)
3.  **Agents** (Live chat handover configuration)
4.  **Channel** (Integration settings - *Placeholder for now*)

---

## 3. Detailed Functional Requirements

### 3.1. Tab: Sources
This tab manages the RAG (Retrieval-Augmented Generation) data. It must have a sub-navigation: **Websites | FAQ | Documents**.

#### A. Sub-tab: Websites
**UI Layout:**
* **Input Section:**
    * Field: `Website Crawler` (Input URL, e.g., https://example.com).
    * Action Button: `Fetch links`.
    * Field: `Add Additional URL` (for specific sub-paths).
* **List Section (Websites List):**
    * Table displaying added URLs.
    * Columns: Link Status (Icon + Link), Training Status (Trained/Untrained), Date/User info, Action (Delete/Trash Icon).
* **Sidebar (Right Panel):**
    * **Header:** "Included Knowledge Sources".
    * **Stats:** Show count of "Trained" vs "Untrained" links.
    * **Global Action:** `Retrain Chatbot` button (bottom of sidebar).

#### B. Sub-tab: Documents
**UI Layout:**
* **Upload Area:**
    * Large drag-and-drop zone.
    * **Copy:** "Drop file here or browse".
    * **Constraints:** "Allowed file extensions: .pdf, .csv | Max file size: 10.00 MB".
* **Sidebar (Right Panel):**
    * Same as the Websites tab (shows included sources stats).

#### C. Sub-tab: FAQ
**UI Layout:**
* **Search Bar:** Filter existing QA pairs.
* **Action:** `+` Button or `Add New FAQ` to open a modal/form for Question & Answer input.
* **List View:** Display table of current Q&A pairs.

---

### 3.2. Tab: Agents
This tab configures the logic for handing over conversations to human agents.

**UI Components:**
1.  **Warning Banner (Conditional):**
    * *Condition:* If WhatsApp/Omnichannel is NOT connected.
    * *Text:* "You didn't have connected WhatsApp on the omnichannel. Please go to Integration Page to connect it."
    * *Style:* Yellow warning alert.

2.  **Master Toggle:**
    * Label: `Activate Live Agents`.
    * Description: "Activate Live Agents When a customer requires human intervention..."

3.  **Configuration Fields (Grid Layout):**
    * **Field 1: Trigger Condition**
        * Type: Textarea.
        * Placeholder/Default: "ONLY USE this intent if the user explicitly say 'I want to talk with an agent'..."
        * Validation: Character counter (e.g., 154/250).
    * **Field 2: Waiting Messages**
        * Type: Textarea.
        * Placeholder/Default: "Thank you for your patience! We're currently connecting you..."
        * Validation: Character counter (e.g., 247/250).

4.  **Footer Action:**
    * Button: `Save Update` (Blue primary button).

---

### 3.3. Tab: Playground
**UI Layout:**
* **Chat Interface:** A standard chat window to test the current configuration.
* **Components:**
    * Chat history view (User bubbles right, Bot bubbles left).
    * Input area.
    * `Clear chat` button.
    * Error handling display (as seen in screenshot: "It seems like your message might be incomplete...").

---

## 4. Technical Specifications & Data Model

### Frontend (React/Vue/Next.js - *Adjust based on project stack*)
* **State Management:**
    * Needs to track `knowledgeSources` array (type, status, content).
    * Needs to track `agentConfig` object (isActive, triggerText, waitingText).
* **Components:** Reusable `Tabs`, `Card`, `Button`, `TextAreaWithCounter`, `Dropzone`.

### Backend Integration Points (Mockup/API Requirements)
* `POST /api/crawler/fetch`: To accept a URL and return sub-links.
* `POST /api/sources/upload`: To handle PDF/CSV parsing.
* `POST /api/agent/config`: To save trigger conditions and messages.
* `POST /api/bot/train`: To trigger the embedding process (Retrain button).

## 5. Acceptance Criteria
1.  **Visual Accuracy:** UI matches the provided screenshots (clean, white background, distinct card sections).
2.  **Validation:** Users cannot upload files >10MB or non-PDF/CSV files. Textareas must stop input at 250 chars.
3.  **Responsiveness:** Sidebar in "Sources" should remain fixed or responsive depending on screen width.
4.  **Interactivity:** "Retrain Chatbot" button should have a loading state.