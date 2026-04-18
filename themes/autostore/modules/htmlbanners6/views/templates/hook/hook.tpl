{if $htmlbanners6.slides}
  <style>
    {foreach from=$htmlbanners6.slides item=slide name='htmlbanners6'}
    {if $slide.customclass && $slide.image_url}{$slide.customclass nofilter} {
    background-image: url({$slide.image_url nofilter});
    background-position: 50% 50%;
    background-repeat: no-repeat;
    -webkit-background-size: cover;
    background-size: cover;
}
    {/if}
    {/foreach}
  </style>
{/if}


