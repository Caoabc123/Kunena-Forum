-- Table migration from Kunena 1.0 and 1.5

-- NOTE: if you run the script manually some queries may fail on older versions
-- but it doesn't matter as update queries will add the missing tables..

-- Kunena 1.0.6 - 1.5.14
CREATE TABLE `#__kunena_announcement`
SELECT *
FROM `#__fb_announcement`;
ALTER TABLE `#__kunena_announcement`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_attachments`
SELECT *
FROM `#__fb_attachments`;
ALTER TABLE `#__kunena_attachments`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_categories`
SELECT *
FROM `#__fb_categories`;
ALTER TABLE `#__kunena_categories`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_favorites`
SELECT *
FROM `#__fb_favorites`;
ALTER TABLE `#__kunena_favorites`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_groups`
SELECT *
FROM `#__fb_groups`;
ALTER TABLE `#__kunena_groups`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_messages`
SELECT *
FROM `#__fb_messages`;
ALTER TABLE `#__kunena_messages`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_messages_text`
SELECT *
FROM `#__fb_messages_text`;
ALTER TABLE `#__kunena_messages_text`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_moderation`
SELECT *
FROM `#__fb_moderation`;
ALTER TABLE `#__kunena_moderation`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_ranks`
SELECT *
FROM `#__fb_ranks`;
ALTER TABLE `#__kunena_ranks`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_sessions`
SELECT *
FROM `#__fb_sessions`;
ALTER TABLE `#__kunena_sessions`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_smileys`
SELECT *
FROM `#__fb_smileys`;
ALTER TABLE `#__kunena_smileys`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_subscriptions`
SELECT *
FROM `#__fb_subscriptions`;
ALTER TABLE `#__kunena_subscriptions`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_users`
SELECT *
FROM `#__fb_users`;
ALTER TABLE `#__kunena_users`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_version`
SELECT *
FROM `#__fb_version`;
ALTER TABLE `#__kunena_version`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__kunena_whoisonline`
SELECT *
FROM `#__fb_whoisonline`;
ALTER TABLE `#__kunena_whoisonline`
    ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;