import type { BlockComponent } from './types';
import CapabilityScroll from './CapabilityScroll';
import CaseReel from './CaseReel';
import CharacterLoop from './CharacterLoop';
import CtaBand from './CtaBand';
import Faq from './Faq';
import FormBlock from './FormBlock';
import HeroCinematic from './HeroCinematic';
import HeroInterior from './HeroInterior';
import JobsList from './JobsList';
import LogoMarquee from './LogoMarquee';
import MediaFull from './MediaFull';
import PostsGrid from './PostsGrid';
import ProcessTriptych from './ProcessTriptych';
import RichText from './RichText';
import StatRow from './StatRow';
import Testimonials from './Testimonials';

export const blockRegistry: Record<string, BlockComponent> = {
    hero_cinematic: HeroCinematic, hero_interior: HeroInterior, case_reel: CaseReel, stat_row: StatRow,
    process_triptych: ProcessTriptych, capability_scroll: CapabilityScroll, logo_marquee: LogoMarquee,
    testimonials: Testimonials, character_loop: CharacterLoop, posts_grid: PostsGrid, jobs_list: JobsList,
    faq: Faq, form: FormBlock, cta_band: CtaBand, rich_text: RichText, media_full: MediaFull,
};
