# Medical Quiz Platform: Cardiac & NLP Simulation
*A comprehensive training platform for clinical diagnosis through audio analysis and semantic evaluation.*

## Introduction
This platform is a specialized medical training tool designed to bridge the gap between theoretical knowledge and practical diagnostic skills. It features two distinct assessment modules: a cardiac audio analysis engine for detecting heart pathologies, and an AI-driven NLP system to evaluate clinical reasoning through semantic analysis of student responses. The platform includes a full administrative dashboard for instructors to manage medical cases, monitor student progress, and grade assessments.

## Architecture Overview
The application is structured into a client-server model using a decoupled architecture for the AI processing module.


## AI Engine: Data & Model Training

To ensure accurate clinical classification, the system uses a fine-tuned version of **DrBERT**, a transformer model pre-trained on French medical corpus.

### 1. Dataset
The model was trained on a categorized clinical dataset. A sample of the input data structure is available in `ai_engine/dataset_sample.csv`:
* **question**: The clinical query posed to the patient.
* **theme**: The semantic category (e.g., `douleur thoracique`, `irradiation`, `antécédents cardiovasculaires`).

### 2. Training Pipeline (`train.py`)
The fine-tuning process is automated via the `train.py` script. Key technical choices include:
* **Model**: [Dr-BERT/DrBERT-4GB](https://huggingface.co/Dr-BERT/DrBERT-4GB) (Specialized for French medical language).
* **Preprocessing**: Automated cleaning of duplicate labels and semantic normalization.
* **Optimization**: Use of `Trainer` API with `load_best_model_at_end=True` to prevent overfitting based on validation loss.
* **Deployment**: The resulting model is saved with a custom `label_mapping.txt` to allow dynamic inference in the Flask microservice.

## Architecture

```text
+-------------------------------------------------------+
|                USER INTERFACE (Browser)               |
|  [Quiz 1: Audio]  [Quiz 2: Audio]  [Quiz 3: AI/NLP]   |
+---------------------------+---------------------------+
                            |
                            v
+---------------------------+---------------------------+
|               PHP WEB SERVER (Apache)                 |
|   - Session Management    - MySQL DB (MP3 Assets)     |
+-------------+---------------------------+-------------+
              |                           |
              | (SQL Queries)             | (cURL / API)
              v                           v
+-------------+-------------+   +-----------------------+
|  MySQL DATABASE (8.0)     |   |   FLASK API (Python)  |
| (Student/Quiz/MP3 Data)   |   | (NLP Engine / DrBERT) |
+---------------------------+   +-----------------------+ 
```


## Installation for Users
1. Clone the repository: `git clone https://github.com/aanis2780/Pulse-Lunges-Platform#architecture`
2. Ensure Docker and Docker Compose are installed on your machine.
3. Run the services: `docker-compose up -d --build`
4. Access the dashboard via your browser at: `http://localhost:8080`

## Installation for Developers
1. Clone the repository and navigate to the project root.
2. **Database:** Import `database/school.sql` into your local MySQL 8.0 instance. Ensure the audio assets (MP3 files) are correctly linked in the database.
3. **Web Environment:** Configure your environment variables in `web/config.php` to connect to the database.
4. **AI Engine(Quiz 3):** 
   - The AI engine runs as a service defined in docker-compose.yml.
   - The container automatically handles dependency installation (pip install -r requirements.txt) and service startup (python app.py) upon execution.
5. Ensure the PHP frontend is configured to communicate with the Flask API endpoint defined in app/config.php (using the service name http://ia:5000/classify as the host).

## Contributor Expectations
- Follow the established PSR-12 coding standard for PHP files.
- Audio assets must be optimized for web playback and correctly referenced in the database.
- Submit bug reports with clear reproduction steps and relevant database logs.
- Branch naming convention: `feature/short-description` or `fix/short-description`.

## Project Status & Limitations
- **Availability:** This project is a private academic repository. Only a small, representative sample of the codebase and datasets has been published on GitHub for demonstration purposes.
- **NLP Performance:** The model (DrBERT) has been fine-tuned on a custom, annotated clinical dataset. While it is highly specialized and efficient for the 8 defined medical categories, it may show limitations in semantic nuance compared to general-purpose LLMs like GPT-4.
- **Language Nuance:** The model is optimized for French medical terminology. It may occasionally struggle with colloquialisms or highly complex sentence structures outside of the provided clinical context.
- **System Constraints:** Audio file latency may occur on low-bandwidth networks during the quiz modules.
- **Environment:** Session synchronization between the PHP frontend and the Flask AI service requires consistent host configuration within the Docker environment.

**Project Note:** This platform is a refactored version of a legacy project, featuring **significant upgrades** as the AI/NLP engine, the deployment architecture (Docker/Docker Compose), and the overall backend stability.

---

*Project developed as part of the Bachelor's Final Year Project.*