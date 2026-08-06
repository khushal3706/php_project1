-- AI Tool Recommendation Portal — Full Schema + 2026 Seed Data
-- Run: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS `ai_tool_portal` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ai_tool_portal`;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `ai_tools`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') DEFAULT 'student',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Password: Admin@1234 (bcrypt hash)
INSERT IGNORE INTO `users` VALUES
(1, 'PortalAdmin', 'admin@silveroakuni.ac.in', '$2y$10$Ns9Zx30OSI8tOwU5sNqi0uX8vjkDS2XYEI3/Z4DwoiY7K89N4FMLq', 'admin', NOW());

CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `slug` VARCHAR(50) DEFAULT 'general'
) ENGINE=InnoDB;

INSERT IGNORE INTO `categories` VALUES
(1, 'Code Assistant', 'AI tools for coding, debugging, and software development', 'code'),
(2, 'Image Generation', 'Text-to-image and AI-powered visual content creation', 'image'),
(3, 'Data Analysis', 'Research, analytics, and data-driven insights platforms', 'data'),
(4, 'Writing and Content', 'AI writing assistants, editing, and content generation', 'writing'),
(5, 'Audio and Video', 'Voice synthesis, music, video generation and editing', 'audio'),
(6, 'AI Agents', 'Autonomous agents, workflow automation, and AI pipelines', 'agent'),
(7, 'Productivity', 'AI tools for task management, calendars, and efficiency', 'productivity'),
(8, 'Education', 'AI tutoring, learning platforms, and study assistants', 'education');

CREATE TABLE `ai_tools` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tool_name` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `url` VARCHAR(255),
  `pricing` ENUM('Free','Freemium','Paid') DEFAULT 'Freemium',
  `category_id` INT,
  `rating` DECIMAL(3,1) DEFAULT 4.5,
  `added_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`added_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 22 real AI tools — all actively maintained and market-leading as of July 2026
INSERT IGNORE INTO `ai_tools` (`id`,`tool_name`,`description`,`url`,`category_id`,`pricing`,`rating`,`added_by`) VALUES
(1, 'Cursor', 'AI-native code editor with agent mode, multi-model support (GPT-5, Claude, Gemini), and full codebase-aware editing. The most popular AI IDE in 2026.', 'https://cursor.com', 1, 'Freemium', 4.7, 1),
(2, 'GPT Image', 'OpenAI image generator integrated into ChatGPT with best-in-class prompt adherence and text rendering. Powers DALL-E 3 engine with GPT-5 Turbo enhancements.', 'https://chatgpt.com', 2, 'Paid', 4.7, 1),
(3, 'Perplexity', 'AI answer engine with real-time web search, cited sources, and deep research mode. Handles complex multi-step queries across any domain.', 'https://perplexity.ai', 3, 'Freemium', 4.8, 1),
(4, 'Grammarly', 'AI writing intelligence platform that checks grammar, tone, clarity, and brand voice consistency across every app you use. Now includes full AI generation.', 'https://grammarly.com', 4, 'Freemium', 4.7, 1),
(5, 'ElevenLabs', 'Industry-leading AI voice synthesis with voice cloning, emotional range, and 29-language support. The standard for realistic AI narration.', 'https://elevenlabs.io', 5, 'Freemium', 4.8, 1),
(6, 'Runway ML', 'AI video generation and editing platform. Gen-4 Alpha produces cinema-quality clips from text, images, or video references.', 'https://runwayml.com', 5, 'Freemium', 4.6, 1),
(7, 'Canva AI', 'All-in-one AI design platform with Magic Studio for generating presentations, social media, logos, and brand assets at scale.', 'https://canva.com', 2, 'Freemium', 4.6, 1),
(8, 'Claude', 'Anthropic AI assistant with best-in-class reasoning, 200K context window, and nuanced writing. Claude 4.7 leads benchmarks on instruction following.', 'https://claude.ai', 1, 'Freemium', 4.9, 1),
(9, 'Gemini', 'Google multimodal AI assistant for research, data analysis, and real-time information. Gemini 2.5 Pro excels at long-context reasoning and code.', 'https://gemini.google.com', 3, 'Free', 4.6, 1),
(10, 'ChatGPT', 'OpenAI flagship chatbot with GPT-5 Turbo, advanced data analysis, web browsing, image generation, and custom GPT agents. The most versatile AI tool.', 'https://chatgpt.com', 4, 'Freemium', 4.9, 1),
(11, 'GitHub Copilot', 'AI code completion for VS Code, JetBrains, and Neovim. Now features agent mode, multi-file editing, and Copilot Workspace for full PR generation.', 'https://github.com/features/copilot', 1, 'Paid', 4.8, 1),
(12, 'Midjourney', 'Premium AI image generation known for artistic, cinematic quality. V7 delivers photorealistic output with consistent character rendering.', 'https://midjourney.com', 2, 'Paid', 4.9, 1),
(13, 'Bolt.new', 'AI that builds full-stack web applications from a single prompt in your browser. Generates, deploys, and hosts production-ready apps instantly.', 'https://bolt.new', 6, 'Freemium', 4.8, 1),
(14, 'Zapier AI', 'No-code AI automation platform connecting 7,000+ apps. Build AI agents that handle email, data entry, CRM updates, and multi-step workflows.', 'https://zapier.com', 6, 'Freemium', 4.6, 1),
(15, 'Replit AI', 'Cloud IDE with an AI agent that builds, deploys, and hosts applications from natural language. Ideal for rapid prototyping and learning.', 'https://replit.com', 6, 'Freemium', 4.7, 1),
(16, 'Gamma AI', 'AI presentation maker that creates professional slides, docs, and landing pages from a single prompt. Used by 50M+ creators worldwide.', 'https://gamma.app', 7, 'Freemium', 4.5, 1),
(17, 'Otter.ai', 'AI meeting assistant that transcribes, summarizes, and generates action items in real time. Integrates with Zoom, Google Meet, and Teams.', 'https://otter.ai', 7, 'Freemium', 4.4, 1),
(18, 'Motion', 'AI-powered project manager and calendar that auto-schedules tasks by priority, deadline, and available time. Rebuilds schedule in real time.', 'https://usemotion.com', 7, 'Paid', 4.3, 1),
(19, 'Khanmigo', 'AI tutor from Khan Academy that guides students using the Socratic method. Studies show 34% greater learning gains vs traditional tutoring.', 'https://khanacademy.org', 8, 'Free', 4.6, 1),
(20, 'Quizlet AI', 'AI study platform with Q-Chat tutor, automatic flashcard generation from notes, and personalized practice tests for exam prep.', 'https://quizlet.com', 8, 'Freemium', 4.5, 1),
(21, 'Duolingo Max', 'AI-powered language learning with role-play conversations, explain-my-answer feedback, and personalized lesson plans powered by GPT-5.', 'https://duolingo.com', 8, 'Paid', 4.7, 1),
(22, 'Notion AI', 'AI writing assistant built into the Notion workspace. Drafts documents, summarizes notes, extracts tasks, and generates content from prompts.', 'https://notion.so', 4, 'Paid', 4.5, 1);

CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `tool_id` INT,
  `rating` TINYINT CHECK (rating >= 1 AND rating <= 5),
  `review_text` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tool_id`) REFERENCES `ai_tools`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
